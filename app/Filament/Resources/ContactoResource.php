<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactoResource\Pages;
// use App\Filament\Resources\ContactoResource\RelationManagers; // Not used for this specific request
use App\Models\Contacto;
use App\Models\Barrio; // Para el selector de barrios
use App\Models\User;   // Para el selector de asesores
use App\Models\Orden; // <-- Import the Orden model
use App\Filament\Resources\OrdenResource; // <-- Import OrdenResource for linking
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
// use Illuminate\Database\Eloquent\SoftDeletingScope; // Not strictly needed for this change if not used elsewhere
use Illuminate\Support\HtmlString; // <-- Import HtmlString
use Filament\Forms\Components\Section; // <-- Import Section for grouping

class ContactoResource extends Resource
{
    protected static ?string $model = Contacto::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';
    protected static ?string $navigationGroup = 'Gestión de Clientes';
    protected static ?string $recordTitleAttribute = 'nombre_completo';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('nombre_completo')
                    ->required()
                    ->maxLength(100)
                    ->columnSpanFull(),
                Forms\Components\TextInput::make('cedula')
                    ->label('Cédula o NIT')
                    ->maxLength(20)
                    ->unique(Contacto::class, 'cedula', ignoreRecord: true)
                    ->nullable(),
                Forms\Components\TextInput::make('direccion')
                    ->required()
                    ->maxLength(150),
                Forms\Components\Select::make('id_barrio')
                    ->label('Barrio')
                    ->options(Barrio::all()->pluck('nombre', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\TextInput::make('celular')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),
                Forms\Components\TextInput::make('telefono_fijo')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),
                Forms\Components\TextInput::make('telefono_opcional')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),
                Forms\Components\Select::make('tipo_cliente')
                    ->options([
                        'INTERESA' => 'Interesa',
                        'NO_INTERESA' => 'No Interesa',
                        'TIENE_TECNICO' => 'Tiene Técnico',
                        'FUERA_DE_MEDELLIN' => 'Fuera de Medellín',
                        'MALO' => 'Malo',
                        'INACTIVO' => 'Inactivo',
                        'INVITAR_NUEVAMENTE' => 'Invitar Nuevamente',
                        'REPROGRAMAR' => 'Reprogramar',
                        'VENTA' => 'Venta',
                        'INICIAL' => 'Inicial',
                    ])
                    ->required(),
                Forms\Components\Select::make('id_asesor')
                    ->label('Asesor Asignado')
                    ->options(User::whereIn('rol', ['ASESOR', 'ADMIN', 'SUPER_ADMIN'])->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\Textarea::make('observaciones_cliente')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(),

                // --- Seccion para mostrar las ordenes del contacto ---
                Section::make('Órdenes del Contacto')
                    ->description('Listado de órdenes de servicio asociadas a este contacto.')
                    ->collapsible()
                    ->collapsed(false) // Puedes ponerlo en true para que inicie colapsado
                    ->visible(fn ($record) => $record && $record->exists) // Solo visible en modo edición y si el registro existe
                    ->columnSpanFull() // Para que ocupe todo el ancho
                    ->schema([
                        Forms\Components\Placeholder::make('ordenes_del_contacto_list')
                            ->label('') // La sección ya tiene título
                            ->content(function ($record) {
                                if (!$record || !method_exists($record, 'ordenes')) {
                                    // Asegurarse que el método 'ordenes' exista en el modelo Contacto
                                    return 'No se pueden cargar las órdenes (relación no definida).';
                                }

                                // Asume que tienes una relación `ordenes()` definida en tu modelo Contacto
                                // Ejemplo: public function ordenes() { return $this->hasMany(Orden::class, 'id_contacto'); }
                                $ordenes = $record->ordenes()->orderBy('fecha_servicio_programada', 'desc')->get();

                                if ($ordenes->isEmpty()) {
                                    return new HtmlString('<p class="text-gray-500 dark:text-gray-400 py-3 text-center">No hay órdenes para este contacto.</p>');
                                }

                                $htmlList = '<div class="border-gray-200 dark:border-gray-700 rounded-md mt-2">';
                                $htmlList .= '<ul class="divide-y divide-gray-200 dark:divide-gray-700">';

                                foreach ($ordenes as $index => $orden) {
                                    $isLast = $index === $ordenes->count() - 1;
                                    $ordenUrl = OrdenResource::getUrl('view', ['record' => $orden->id]);
                                    $fechaProgramada = $orden->fecha_servicio_programada ? \Carbon\Carbon::parse($orden->fecha_servicio_programada)->format('d/m/Y') : 'N/A';
                                    $estadoOrden = htmlspecialchars($orden->estado_orden ?? 'N/A');
                                    $numeroOrden = htmlspecialchars($orden->numero_orden ?? 'N/A');
                                
                                    $htmlList .= '<li class="px-4 py-3" style="'
                                        . ($isLast ? '' : 'margin-bottom: 20px;')
                                        . 'border: 1px solid #2e2e31; padding: 16px 20px; border-radius: 8px;">';
                                
                                    $htmlList .= '<div class="flex items-center justify-between">';
                                    $htmlList .= '<div>';
                                    $htmlList .= '<p class="text-sm font-medium text-gray-900 dark:text-white">Orden: ' . $numeroOrden . '</p>';
                                    $htmlList .= '<p class="text-xs text-gray-600 dark:text-gray-400">Estado: ' . $estadoOrden . ' - Fecha Prog.: ' . $fechaProgramada . '</p>';
                                    $htmlList .= '</div>';
                                    $htmlList .= '<a href="' . $ordenUrl . '" target="_blank" class="inline-flex items-center px-2.5 py-1.5 border border-transparent text-xs font-medium rounded shadow-sm text-white bg-primary-600 hover:bg-primary-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary-500 dark:bg-primary-500 dark:hover:bg-primary-400">';
                                    $htmlList .= 'Ver Detalles';
                                    $htmlList .= '</a>';
                                    $htmlList .= '</div>';
                                    $htmlList .= '</li>';
                                }
                                
                                $htmlList .= '</ul>';
                                $htmlList .= '</div>';

                                return new HtmlString($htmlList);
                            }),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)->recordAction('edit') // Click en fila abre editar (y por ende el slide-over)
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cedula')
                    ->label('Cédula/NIT')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('celular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barrio.nombre')
                    ->label('Barrio')
                    ->sortable()
                    ->searchable()
                    ->tooltip(fn (Contacto $record): string => "Dirección: {$record->direccion}"),
                Tables\Columns\TextColumn::make('asesor.name')
                    ->label('Asesor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_cliente')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'VENTA' => 'success',
                        'INTERESA' => 'primary',
                        'NO_INTERESA' => 'warning',
                        'MALO' => 'danger',
                        default => 'gray',
                    })
                    ->searchable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Fecha Creación')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime('d/m/Y H:i')
                    ->label('Última Actualización')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('tipo_telefono_filtro')
                ->label('Filtrar por Teléfono')
                ->options([
                    'solo_celular' => 'Solo Celular',
                    'solo_fijo' => 'Solo Teléfono',
                    'ambos' => 'Tiene Celular y Fijo',
                    'ninguno_registrado' => 'Ninguno Registrado',
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value'];

                    if ($value === 'solo_celular') {
                        return $query->whereNotNull('celular')->whereNull('telefono_fijo');
                    }
                    if ($value === 'solo_fijo') {
                        return $query->whereNotNull('telefono_fijo')->whereNull('celular');
                    }
                    if ($value === 'ambos') {
                        return $query->whereNotNull('celular')->whereNotNull('telefono_fijo');
                    }
                    if ($value === 'ninguno_registrado') {
                        return $query->whereNull('celular')->whereNull('telefono_fijo')->whereNull('telefono_opcional');
                    }
                    return $query;
                }),
                Tables\Filters\SelectFilter::make('id_barrio')
                    ->label('Barrio')
                    ->relationship('barrio', 'nombre'),
                Tables\Filters\SelectFilter::make('tipo_cliente')
                    ->options([
                        'INTERESA' => 'Interesa',
                        'NO_INTERESA' => 'No Interesa',
                        'TIENE_TECNICO' => 'Tiene Técnico',
                        'FUERA_DE_MEDELLIN' => 'Fuera de Medellín',
                        'MALO' => 'Malo',
                        'INACTIVO' => 'Inactivo',
                        'INVITAR_NUEVAMENTE' => 'Invitar Nuevamente',
                        'REPROGRAMAR' => 'Reprogramar',
                        'VENTA' => 'Venta',
                        'INICIAL' => 'Inicial',
                    ]),
                Tables\Filters\SelectFilter::make('id_asesor')
                    ->label('Asesor')
                    ->options(User::whereIn('rol', ['ASESOR', 'ADMIN', 'SUPER_ADMIN'])->pluck('name', 'id')),
            ])
            ->actions([
                Tables\Actions\EditAction::make()
                ->slideOver() // Mantiene el slide-over para editar
                ->modalWidth('2xl') // Ajusta el ancho del slide-over si es necesario ('xl', '2xl', '3xl', etc.)
                ->modalHeading('Editar Contacto'),
                Tables\Actions\DeleteAction::make(),
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
            // RelationManagers no son necesarios para esta visualización directa en el formulario.
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListContactos::route('/'),
            'create' => Pages\CreateContacto::route('/create'),
            'edit' => Pages\EditContacto::route('/{record}/edit'),
        ];
    }
}