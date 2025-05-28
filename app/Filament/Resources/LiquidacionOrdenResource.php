<?php

namespace App\Filament\Resources;

use App\Filament\Resources\LiquidacionOrdenResource\Pages;
use App\Filament\Resources\LiquidacionOrdenResource\RelationManagers;
use App\Models\LiquidacionOrden;
use App\Models\Orden;
use App\Models\Tecnico;
use App\Models\User; // Para el usuario que revisa
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Support\Facades\Log; // Para depuración si es necesario

class LiquidacionOrdenResource extends Resource
{
    protected static ?string $model = LiquidacionOrden::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';
    protected static ?string $navigationGroup = 'Finanzas';
    protected static ?string $modelLabel = 'Liquidación de Orden';
    protected static ?string $pluralModelLabel = 'Liquidaciones de Órdenes';
    protected static ?string $recordTitleAttribute = 'id'; // O podrías construir uno más descriptivo

    // Dentro de la clase LiquidacionOrdenResource
    protected static function calcularValoresLiquidacion(array $data): array
    {
        $repuestos = $data['repuestosGastados'] ?? [];
        $totalRepuestos = 0;
        foreach ($repuestos as $repuesto) {
            $cantidad = !empty($repuesto['cantidad']) ? floatval($repuesto['cantidad']) : 0;
            $precio = !empty($repuesto['precio_unitario_compra']) ? floatval($repuesto['precio_unitario_compra']) : 0;
            $totalRepuestos += $cantidad * $precio;
        }
        $data['monto_total_repuestos'] = $totalRepuestos;

        $efectivo = floatval($data['monto_cobrado_efectivo'] ?? 0);
        $transferencia = floatval($data['monto_cobrado_transferencia'] ?? 0);
        $otrosGastos = floatval($data['monto_otros_gastos'] ?? 0);
        $data['saldo_neto_liquidacion'] = ($efectivo + $transferencia) - ($totalRepuestos + $otrosGastos);

        return $data;
    }

    public static function mutateFormDataBeforeCreate(array $data): array
    {
        $data = self::calcularValoresLiquidacion($data);

        // Actualizar estado de la orden asociada
        if (isset($data['id_orden'])) {
            $orden = Orden::find($data['id_orden']);
            if ($orden) {
                $orden->estado_orden = 'LIQUIDACION_EN_REVISION'; // O el estado que definas
                $orden->save();
            }
        }
        return $data;
    }

    public static function mutateFormDataBeforeSave(array $data): array // Para la edición
    {
        return self::calcularValoresLiquidacion($data);
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información de la Orden y Técnico')
                    ->columns(2)
                    ->schema([
                        Select::make('id_orden')
                            ->label('Orden a Liquidar')
                            ->relationship('orden', 'numero_orden')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive() // Para que otros campos reaccionen a su cambio
                            ->afterStateUpdated(function (Set $set, ?string $state) {
                                // Autocompletar técnico si la orden tiene uno asignado
                                if ($state) {
                                    $orden = Orden::find($state);
                                    if ($orden && $orden->id_tecnico) {
                                        $set('id_tecnico_liquida', $orden->id_tecnico);
                                    }
                                }
                            })
                            // Podrías filtrar para mostrar solo órdenes 'COMPLETADA' o 'PENDIENTE_LIQUIDACION'
                            // ->options(Orden::whereIn('estado_orden', ['COMPLETADA', 'PENDIENTE_LIQUIDACION'])->doesntHave('liquidacionOrden')->pluck('numero_orden', 'id'))
                            ->disabled(fn (string $operation): bool => $operation !== 'create'), // Deshabilitado en edición

                        Select::make('id_tecnico_liquida')
                            ->label('Técnico que Liquida')
                            ->relationship('tecnicoLiquidador', 'nombre') // Asumiendo 'nombre' en Tecnico, o usa el accesor 'nombre_completo'
                            ->searchable(['nombre', 'apellido', 'cedula'])
                            ->preload()
                            ->required()
                            ->disabled(fn (string $operation): bool => $operation !== 'create'), // Deshabilitado en edición

                        DateTimePicker::make('fecha_liquidacion')
                            ->label('Fecha de Liquidación')
                            ->default(now())
                            ->native(false)
                            ->required(),
                    ]),

                Section::make('Cobros Realizados al Cliente')
                    ->columns(2)
                    ->schema([
                        TextInput::make('monto_cobrado_efectivo')
                            ->label('Monto Cobrado en Efectivo')
                            ->numeric()
                            ->prefix('$')
                            ->inputMode('decimal')
                            ->default(0)
                            ->live(onBlur: true) // actualiza en vivo al perder el foco
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::actualizarTotales($get, $set)),
                        TextInput::make('monto_cobrado_transferencia')
                            ->label('Monto Cobrado por Transferencia')
                            ->numeric()
                            ->prefix('$')
                            ->inputMode('decimal')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::actualizarTotales($get, $set)),
                        TextInput::make('referencia_transferencia')
                            ->label('Referencia de Transferencia')
                            ->maxLength(100)
                            ->nullable(),
                        Textarea::make('observaciones_cobro')
                            ->label('Observaciones del Cobro')
                            ->columnSpanFull()
                            ->nullable(),
                    ]),

                Section::make('Gastos del Técnico')
                    ->schema([
                        Repeater::make('repuestosGastados') // Nombre de la relación en LiquidacionOrden
                            ->relationship() // Indica que es una relación (para editar)
                            ->label('Repuestos Gastados')
                            ->schema([
                                TextInput::make('nombre_repuesto')
                                    ->label('Nombre del Repuesto')
                                    ->required()
                                    ->maxLength(255)
                                    ->columnSpan(2), // Más ancho para el nombre
                                TextInput::make('cantidad')
                                    ->numeric()
                                    ->required()
                                    ->default(1)
                                    ->minValue(1)
                                    ->columnSpan(1),
                                TextInput::make('precio_unitario_compra')
                                    ->label('Precio Unitario Compra')
                                    ->numeric()
                                    ->required()
                                    ->prefix('$')
                                    ->inputMode('decimal')
                                    ->columnSpan(1),
                            ])
                            ->columns(4)
                            ->addActionLabel('Añadir Repuesto Gastado')
                            ->defaultItems(0) // No empezar con items por defecto en creación, sí en edición si hay datos
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => ($state['nombre_repuesto'] ?? 'Nuevo Repuesto') . ' (x' . ($state['cantidad'] ?? 0) . ')')
                            ->live()
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::actualizarTotales($get, $set))
                            ->deleteAction(
                                fn (Forms\Components\Actions\Action $action) => $action->after(fn (Get $get, Set $set) => self::actualizarTotales($get, $set)),
                            ),
                        TextInput::make('monto_otros_gastos')
                            ->label('Monto Otros Gastos (Transporte, etc.)')
                            ->numeric()
                            ->prefix('$')
                            ->inputMode('decimal')
                            ->default(0)
                            ->live(onBlur: true)
                            ->afterStateUpdated(fn (Get $get, Set $set) => self::actualizarTotales($get, $set)),
                        Textarea::make('descripcion_otros_gastos')
                            ->label('Descripción Otros Gastos')
                            ->nullable(),
                    ]),

                Section::make('Resumen y Estado de Liquidación')
                    ->columns(2)
                    ->schema([
                        TextInput::make('monto_total_repuestos')
                            ->label('Total Gastos en Repuestos')
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false), // No se guarda, se calcula
                        TextInput::make('saldo_neto_liquidacion')
                            ->label('Saldo Neto Liquidación')
                            ->prefix('$')
                            ->disabled()
                            ->dehydrated(false), // No se guarda, se calcula

                        Select::make('estado_liquidacion')
                            ->label('Estado de la Liquidación')
                            ->options([
                                'PENDIENTE_ENTREGA' => 'Pendiente de Entrega (Técnico)',
                                'ENTREGADA_PENDIENTE_REVISION' => 'Entregada / Pendiente Revisión (Admin)',
                                'APROBADA' => 'Aprobada',
                                'RECHAZADA_AJUSTES' => 'Rechazada / Requiere Ajustes',
                                'CERRADA' => 'Cerrada (Pagada/Finalizada)',
                            ])
                            ->default('PENDIENTE_ENTREGA')
                            ->required()
                            // Lógica de visibilidad/deshabilitado según rol
                            ->disabled(fn (string $operation, Get $get): bool =>
                                $operation === 'edit' && !auth()->user()->isAdmin() && $get('estado_liquidacion') !== 'PENDIENTE_ENTREGA'
                            ),
                        Textarea::make('observaciones_internas_liquidacion')
                            ->label('Observaciones Internas (Admin)')
                            ->columnSpanFull()
                            ->nullable()
                            ->visible(fn (): bool => auth()->user()->isAdmin()), // Solo visible para admins
                        // Campos de revisión (se podrían llenar con una acción de "Aprobar")
                        // Select::make('id_usuario_revisa')->label('Revisado Por')->relationship('usuarioRevisor', 'name')->disabled(),
                        // DateTimePicker::make('fecha_revision')->label('Fecha Revisión')->disabled(),
                    ]),
            ]);
    }

    public static function actualizarTotales(Get $get, Set $set): void
    {
        $repuestos = $get('repuestosGastados') ?? [];
        $totalRepuestos = 0;
        foreach ($repuestos as $repuesto) {
            // Asegurarse de que los valores no sean null o string vacío antes de la conversión
            $cantidad = !empty($repuesto['cantidad']) ? floatval($repuesto['cantidad']) : 0;
            $precio = !empty($repuesto['precio_unitario_compra']) ? floatval($repuesto['precio_unitario_compra']) : 0;
            $totalRepuestos += $cantidad * $precio;
        }
        $set('monto_total_repuestos', number_format($totalRepuestos, 2, '.', ''));

        $otrosGastos = floatval($get('monto_otros_gastos') ?? 0);
        $efectivo = floatval($get('monto_cobrado_efectivo') ?? 0);
        $transferencia = floatval($get('monto_cobrado_transferencia') ?? 0);

        $saldo = ($efectivo + $transferencia) - ($totalRepuestos + $otrosGastos);
        $set('saldo_neto_liquidacion', number_format($saldo, 2, '.', ''));
    }


    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('orden.numero_orden')
                    ->label('N° Orden')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('orden.asesorCreador.name') // <--- ¡ESTA ES LA NUEVA MAGIA!
                    ->label('Asesor Creó Orden')
                    ->searchable() // Podrías querer buscar por el nombre del asesor
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('tecnicoLiquidador.nombre_completo') // Usando el accesor del modelo Tecnico
                    ->label('Técnico')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('fecha_liquidacion')
                    ->label('Fecha Liquidación')
                    ->dateTime('d/m/Y H:i')
                    ->sortable(),
                TextColumn::make('monto_cobrado_efectivo')
                    ->label('Cobro Efectivo')
                    ->money('COP') // Asume pesos colombianos
                    ->sortable(),
                TextColumn::make('monto_cobrado_transferencia')
                    ->label('Cobro Transf.')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('monto_total_repuestos')
                    ->label('Total Repuestos')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('monto_otros_gastos')
                    ->label('Otros Gastos')
                    ->money('COP')
                    ->sortable(),
                TextColumn::make('saldo_neto_liquidacion')
                    ->label('Saldo Neto')
                    ->money('COP')
                    ->sortable()
                    ->color(fn ($state) => $state >= 0 ? 'success' : 'danger'),
                TextColumn::make('estado_liquidacion')
                    ->label('Estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE_ENTREGA' => 'warning',
                        'ENTREGADA_PENDIENTE_REVISION' => 'info',
                        'APROBADA' => 'primary',
                        'RECHAZADA_AJUSTES' => 'danger',
                        'CERRADA' => 'success',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                TextColumn::make('usuarioRevisor.name')
                    ->label('Revisado Por')
                    ->default('-')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('id_tecnico_liquida')
                    ->label('Técnico')
                    ->relationship('tecnicoLiquidador', 'nombre'), // Ajustar si usas accesor
                SelectFilter::make('estado_liquidacion')
                    ->label('Estado Liquidación')
                    ->options([
                        'PENDIENTE_ENTREGA' => 'Pendiente de Entrega',
                        'ENTREGADA_PENDIENTE_REVISION' => 'Pendiente Revisión',
                        'APROBADA' => 'Aprobada',
                        'RECHAZADA_AJUSTES' => 'Rechazada',
                        'CERRADA' => 'Cerrada',
                    ]),
            ])
            ->actions([
                Tables\Actions\EditAction::make()->modalWidth('5xl'), // Abrir edición en modal grande
                Tables\Actions\ViewAction::make(),
                // Acción para Aprobar/Rechazar (visible para admins)
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\Action::make('aprobar')
                        ->label('Aprobar')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->requiresConfirmation()
                        ->action(function (LiquidacionOrden $record) {
                            $record->estado_liquidacion = 'APROBADA';
                            $record->id_usuario_revisa = auth()->id();
                            $record->fecha_revision = now();
                            $record->save();
                            // Cambiar estado de la orden principal
                            $record->orden()->update(['estado_orden' => 'LIQUIDADA_CERRADA']); // O un estado intermedio
                        })
                        ->visible(fn (LiquidacionOrden $record): bool =>
                            auth()->user()->isAdmin() && $record->estado_liquidacion === 'ENTREGADA_PENDIENTE_REVISION'
                        ),
                    Tables\Actions\Action::make('rechazar')
                        ->label('Rechazar/Ajustes')
                        ->icon('heroicon-o-x-circle')
                        ->color('danger')
                        ->requiresConfirmation()
                        ->modalHeading('Rechazar Liquidación')
                        ->modalDescription('Por favor, indica el motivo del rechazo o los ajustes necesarios.')
                        ->form([
                            Textarea::make('motivo_rechazo')
                                ->label('Motivo del Rechazo / Ajustes Requeridos')
                                ->required(),
                        ])
                        ->action(function (LiquidacionOrden $record, array $data) {
                            $record->estado_liquidacion = 'RECHAZADA_AJUSTES';
                            $record->observaciones_internas_liquidacion = ($record->observaciones_internas_liquidacion ? $record->observaciones_internas_liquidacion . "\n" : '') .
                                                                        "Rechazada por: " . auth()->user()->name . " - Motivo: " . $data['motivo_rechazo'];
                            $record->id_usuario_revisa = auth()->id();
                            $record->fecha_revision = now();
                            $record->save();
                        })
                        ->visible(fn (LiquidacionOrden $record): bool =>
                            auth()->user()->isAdmin() && $record->estado_liquidacion === 'ENTREGADA_PENDIENTE_REVISION'
                        ),
                ])->visible(fn (LiquidacionOrden $record): bool => auth()->user()->isAdmin()),
            ])
            ->bulkActions([
                // Tables\Actions\BulkActionGroup::make([
                //     Tables\Actions\DeleteBulkAction::make(),
                // ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListLiquidacionOrdenes::route('/'), // O ListLiquidacionesOrdenes
            'create' => Pages\CreateLiquidacionOrden::route('/create'), // O CreateLiquidacionOrden
            'edit' => Pages\EditLiquidacionOrden::route('/{record}/edit'), // O EditLiquidacionOrden
            'view' => Pages\ViewLiquidacionOrden::route('/{record}'), // O ViewLiquidacionOrden
        ];
    }

    // Lógica para que al crear una liquidación se actualice el estado de la orden
    // y al guardar la liquidación, se recalculen y guarden los montos.
    // Esto se puede hacer en el Page\CreateLiquidacionOrden y Page\EditLiquidacionOrden
    // o usando Observers en el modelo LiquidacionOrden.

    // Ejemplo de cómo podrías hacerlo en las páginas (más explícito):
    // En App\Filament\Resources\LiquidacionOrdenResource\Pages\CreateLiquidacionOrden.php
    // protected function handleRecordCreation(array $data): Model
    // {
    //     // Calcular totales antes de crear
    //     $repuestos = $data['repuestosGastados'] ?? [];
    //     $totalRepuestos = 0;
    //     foreach ($repuestos as $repuesto) {
    //         $totalRepuestos += floatval($repuesto['cantidad']) * floatval($repuesto['precio_unitario_compra']);
    //     }
    //     $data['monto_total_repuestos'] = $totalRepuestos;

    //     $efectivo = floatval($data['monto_cobrado_efectivo'] ?? 0);
    //     $transferencia = floatval($data['monto_cobrado_transferencia'] ?? 0);
    //     $otrosGastos = floatval($data['monto_otros_gastos'] ?? 0);
    //     $data['saldo_neto_liquidacion'] = ($efectivo + $transferencia) - ($totalRepuestos + otrosGastos);

    //     $liquidacion = static::getModel()::create($data);

    //     // Actualizar estado de la orden
    //     if ($liquidacion->id_orden) {
    //         $orden = Orden::find($liquidacion->id_orden);
    //         if ($orden) {
    //             $orden->estado_orden = 'LIQUIDACION_EN_REVISION'; // O el estado que corresponda
    //             $orden->save();
    //         }
    //     }
    //     return $liquidacion;
    // }
    // Similar lógica en EditLiquidacionOrden.php en el método handleRecordUpdate
}