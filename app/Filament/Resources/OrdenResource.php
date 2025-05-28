<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OrdenResource\Pages;
use App\Filament\Resources\OrdenResource\RelationManagers;
use App\Models\Orden;
use App\Models\Contacto;
use App\Models\Tecnico;
use App\Models\User;
use App\Models\Barrio;
use App\Models\Electrodomestico;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TimePicker;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Section;
use Filament\Forms\Get; // Para lógica condicional en formularios

class OrdenResource extends Resource
{
    protected static ?string $model = Orden::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';
    protected static ?string $navigationGroup = 'Gestión de Servicios';
    protected static ?string $modelLabel = 'Orden de Servicio';
    protected static ?string $pluralModelLabel = 'Órdenes de Servicio';
    protected static ?string $recordTitleAttribute = 'numero_orden';


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Información del Cliente y Servicio')
                    ->columns(2)
                    ->schema([
                        Select::make('id_contacto')
                            ->label('Cliente')
                            ->relationship('contacto', 'nombre_completo')
                            ->searchable(['nombre_completo', 'cedula', 'celular'])
                            ->preload()
                            ->required()
                            ->createOptionForm([ // Modal para crear nuevo contacto
                                TextInput::make('nombre_completo')->required()->maxLength(100),
                                TextInput::make('cedula')->label('Cédula/NIT')->maxLength(20)->unique(Contacto::class, 'cedula', ignoreRecord: true)->nullable(),
                                TextInput::make('direccion')->required()->maxLength(150),
                                Select::make('id_barrio')->label('Barrio')->options(Barrio::all()->pluck('nombre', 'id'))->searchable()->nullable(),
                                TextInput::make('celular')->tel()->required()->maxLength(20),
                                TextInput::make('telefono_fijo')->tel()->maxLength(20)->nullable(),
                                Select::make('id_asesor')
                                    ->label('Asesor Asignado (Auto)')
                                    ->options(User::where('id', auth()->id())->pluck('name', 'id')) // Solo el asesor actual
                                    ->default(auth()->id()) // Asigna el asesor actual por defecto
                                    ->disabled() // No editable en el modal de contacto, se gestiona aparte si es necesario
                                    ->required(),
                                Select::make('tipo_cliente')
                                    ->options(fn() => collect(config('enums.tipo_cliente'))->pluck('label', 'value')) // Asumiendo que tienes un config/enums.php
                                    ->default('INICIAL')
                                    ->required(),

                            ])
                            ->createOptionAction(function (Forms\Components\Actions\Action $action) {
                                return $action
                                    ->modalHeading('Crear Nuevo Contacto')
                                    ->modalButton('Crear Contacto')
                                    ->modalWidth('3xl');
                            }),

                        TextInput::make('direccion_servicio')
                            ->label('Dirección del Servicio')
                            ->required()
                            ->maxLength(255)
                            ->columnSpan(fn (Get $get) => $get('id_contacto') ? 1 : 2), // Ocupa todo el ancho si no hay contacto

                        Select::make('id_barrio_servicio')
                            ->label('Barrio del Servicio')
                            ->options(Barrio::all()->pluck('nombre', 'id'))
                            ->searchable()
                            ->nullable(),

                        // Campo para autocompletar dirección y barrio si se selecciona un contacto
                        // Se podría implementar con un ->reactive() en id_contacto y un ->afterStateUpdated()
                        // Por ahora, se llenan manualmente o se copian.

                        Select::make('id_tecnico')
                            ->label('Técnico Asignado')
                            ->relationship('tecnico', 'nombre') // Asume que Tecnico tiene 'nombre', mejor 'nombre_completo' si lo tienes
                            ->searchable(['nombre', 'apellido', 'cedula']) // Busca por estos campos en Tecnico
                            ->preload()
                            ->nullable(),

                        TextInput::make('numero_orden')
                            ->label('Número de Orden')
                            ->default(fn () => 'ORD-' . strtoupper(uniqid())) // Genera un N° de orden único
                            ->required()
                            ->maxLength(25)
                            ->unique(Orden::class, 'numero_orden', ignoreRecord: true),

                        Select::make('tipo_servicio')
                            ->options([
                                'MANTENIMIENTO' => 'Mantenimiento',
                                'REPARACION' => 'Reparación',
                                'REVISION' => 'Revisión',
                                'GARANTIA' => 'Garantía',
                            ])
                            ->required(),
                        Select::make('estado_orden')
                            ->options([
                                'PENDIENTE_ASIGNAR' => 'Pendiente Asignar',
                                'ASIGNADA' => 'Asignada',
                                'EN_PROCESO' => 'En Proceso',
                                'COMPLETADA' => 'Completada',
                                'PENDIENTE_LIQUIDACION' => 'Pendiente Liquidación',
                                'LIQUIDACION_EN_REVISION' => 'Liquidación en Revisión',
                                'LIQUIDADA_CERRADA' => 'Liquidada y Cerrada',
                                'CANCELADA' => 'Cancelada',
                                'REPROGRAMADA_INTERNAMENTE' => 'Reprogramada Internamente',
                                'REPROGRAMADA_CLIENTE' => 'Reprogramada por Cliente',
                                'REQUIERE_COTIZACION' => 'Requiere Cotización',
                                'COTIZACION_APROBADA' => 'Cotización Aprobada',
                            ])
                            ->default('PENDIENTE_ASIGNAR')
                            ->required(),
                        DatePicker::make('fecha_servicio_programada')
                            ->label('Fecha Programada')
                            ->native(false), // Para mejor experiencia de usuario
                        TimePicker::make('hora_servicio_programada')
                            ->label('Hora Programada')
                            ->seconds(false) // Sin segundos
                            ->native(false),
                        DateTimePicker::make('fecha_servicio_realizada')
                            ->label('Fecha Realización Servicio')
                            ->native(false)
                            ->nullable(),
                        TextInput::make('precio_acordado')
                            ->label('Precio Acordado')
                            ->prefix('$')
                            ->numeric()
                            ->inputMode('decimal')
                            ->nullable(),
                        Textarea::make('observaciones_servicio')
                            ->label('Observaciones del Servicio')
                            ->columnSpanFull()
                            ->nullable(),
                        // El id_asesor_creo se puede llenar automáticamente
                        Forms\Components\Hidden::make('id_asesor_creo')
                            ->default(auth()->id()),
                    ]),

                Section::make('Electrodomésticos a Revisar/Reparar')
                    ->collapsible()
                    ->schema([
                        Repeater::make('ordenElectrodomesticos') // Nombre de la relación en el modelo Orden
                            ->relationship() // Indica que es una relación
                            ->label('Electrodomésticos')
                            ->schema([
                                Select::make('id_electrodomestico')
                                    ->label('Electrodoméstico')
                                    ->options(Electrodomestico::all()->pluck('nombre', 'id'))
                                    ->searchable()
                                    ->required()
                                    ->columnSpan(1),
                                TextInput::make('marca_especifica')
                                    ->label('Marca')
                                    ->maxLength(100)
                                    ->columnSpan(1),
                                TextInput::make('modelo_especifico')
                                    ->label('Modelo')
                                    ->maxLength(100)
                                    ->columnSpan(1),
                                TextInput::make('serie_electrodomestico')
                                    ->label('Serie')
                                    ->maxLength(100)
                                    ->nullable()
                                    ->columnSpan(1),
                                Textarea::make('descripcion_falla_especifica')
                                    ->label('Falla Reportada')
                                    ->rows(2)
                                    ->columnSpanFull(),
                                Textarea::make('diagnostico_tecnico_item')
                                    ->label('Diagnóstico Técnico')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->nullable(),
                                Textarea::make('trabajo_realizado_item')
                                    ->label('Trabajo Realizado')
                                    ->rows(2)
                                    ->columnSpanFull()
                                    ->nullable(),
                            ])
                            ->columns(2) // Cuántas columnas dentro de cada item del repeater
                            ->addActionLabel('Añadir Electrodoméstico')
                            ->defaultItems(1) // Empieza con un item por defecto
                            ->reorderableWithButtons()
                            ->collapsible()
                            ->itemLabel(fn (array $state): ?string => $state['marca_especifica'] ?? null),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('numero_orden')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('contacto.nombre_completo')
                    ->label('Cliente')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('tecnico.nombre_completo') // Usando el accesor del modelo Tecnico
                    ->label('Técnico')
                    ->default('Sin asignar')
                    ->sortable(),
                Tables\Columns\TextColumn::make('estado_orden')
                    ->label('Estado')
                    ->badge()
                     ->color(fn (string $state): string => match ($state) {
                        'PENDIENTE_ASIGNAR' => 'gray',
                        'ASIGNADA' => 'warning',
                        'EN_PROCESO' => 'primary',
                        'COMPLETADA' => 'success',
                        'PENDIENTE_LIQUIDACION' => 'info',
                        'LIQUIDACION_EN_REVISION' => 'primary',
                        'LIQUIDADA_CERRADA' => 'success',
                        'CANCELADA' => 'danger',
                        'REPROGRAMADA_INTERNAMENTE' => 'warning',
                        'REPROGRAMADA_CLIENTE' => 'warning',
                        'REQUIERE_COTIZACION' => 'info',
                        'COTIZACION_APROBADA' => 'indigo',
                        default => 'gray',
                    })
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('fecha_servicio_programada')
                    ->label('Fecha Programada')
                    ->date('d/m/Y')
                    ->sortable(),
                Tables\Columns\TextColumn::make('asesorCreador.name')
                    ->label('Asesor Creó')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Fecha Creación Orden')
                    ->dateTime('d/m/Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('id_tecnico')
                    ->label('Técnico')
                    ->relationship('tecnico', 'nombre'), // Asume que el modelo Tecnico tiene 'nombre' o ajusta al accesor si es necesario
                Tables\Filters\SelectFilter::make('estado_orden')
                    ->label('Estado')
                    ->options([
                        'PENDIENTE_ASIGNAR' => 'Pendiente Asignar',
                        'ASIGNADA' => 'Asignada',
                        'EN_PROCESO' => 'En Proceso',
                        'COMPLETADA' => 'Completada',
                        'PENDIENTE_LIQUIDACION' => 'Pendiente Liquidación',
                        'LIQUIDACION_EN_REVISION' => 'Liquidación en Revisión',
                        'LIQUIDADA_CERRADA' => 'Liquidada y Cerrada',
                        'CANCELADA' => 'Cancelada',
                        // ... otros estados
                    ]),
                Tables\Filters\Filter::make('fecha_servicio_programada')
                    ->form([
                        DatePicker::make('programada_desde')->label('Programada Desde'),
                        DatePicker::make('programada_hasta')->label('Programada Hasta'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['programada_desde'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_servicio_programada', '>=', $date),
                            )
                            ->when(
                                $data['programada_hasta'],
                                fn (Builder $query, $date): Builder => $query->whereDate('fecha_servicio_programada', '<=', $date),
                            );
                    })
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\ViewAction::make(),
                // Podrías añadir una acción para ir a la liquidación
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            // Aquí es donde más adelante registrarías el RelationManager para LiquidacionOrden
            // RelationManagers\LiquidacionOrdenRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrdenes::route('/'),
            'create' => Pages\CreateOrden::route('/create'),
            'edit' => Pages\EditOrden::route('/{record}/edit'),
            'view' => Pages\ViewOrden::route('/{record}'), // Añadí la página de vista
        ];
    }
}