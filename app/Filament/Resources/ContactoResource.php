<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ContactoResource\Pages;
use App\Filament\Resources\ContactoResource\RelationManagers;
use App\Models\Contacto;
use App\Models\Barrio; // Para el selector de barrios
use App\Models\User;   // Para el selector de asesores
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ContactoResource extends Resource
{
    protected static ?string $model = Contacto::class;

    protected static ?string $navigationIcon = 'heroicon-o-users'; // Ícono para el menú
    protected static ?string $navigationGroup = 'Gestión de Clientes'; // Agrupar en el menú (opcional)
    protected static ?string $recordTitleAttribute = 'nombre_completo'; // Para búsquedas globales

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
                    ->unique(Contacto::class, 'cedula', ignoreRecord: true) // Único, pero ignora el registro actual al editar
                    ->nullable(),
                Forms\Components\TextInput::make('direccion')
                    ->required()
                    ->maxLength(150),
                Forms\Components\Select::make('id_barrio')
                    ->label('Barrio')
                    ->options(Barrio::all()->pluck('nombre', 'id')) // Carga barrios para seleccionar
                    ->searchable()
                    ->nullable(),
                Forms\Components\TextInput::make('celular')
                    ->tel() // Tipo teléfono
                    ->maxLength(20)
                    ->nullable(), // Recuerda que la DB obliga al menos uno, la validación de Laravel lo reforzará
                Forms\Components\TextInput::make('telefono_fijo')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),
                Forms\Components\TextInput::make('telefono_opcional')
                    ->tel()
                    ->maxLength(20)
                    ->nullable(),
                Forms\Components\Select::make('tipo_cliente')
                    ->options([ // Opciones del ENUM
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
                    // Carga usuarios con rol ASESOR o ADMIN/SUPER_ADMIN para seleccionar
                    ->options(User::whereIn('rol', ['ASESOR', 'ADMIN', 'SUPER_ADMIN'])->pluck('name', 'id'))
                    ->searchable()
                    ->nullable(),
                Forms\Components\Textarea::make('observaciones_cliente')
                    ->maxLength(65535)
                    ->columnSpanFull()
                    ->nullable(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->recordUrl(null)->recordAction('edit')
            ->columns([
                Tables\Columns\TextColumn::make('nombre_completo')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('cedula')
                    ->label('Cédula/NIT')
                    ->searchable()
                    ->toggleable(isToggledHiddenByDefault: true), // Oculto por defecto, se puede mostrar
                Tables\Columns\TextColumn::make('celular')
                    ->searchable(),
                Tables\Columns\TextColumn::make('barrio.nombre') // Accede al nombre del barrio a través de la relación
                    ->label('Barrio')
                    ->sortable()
                    ->searchable()
                    ->tooltip(fn (Contacto $record): string => "Dirección: {$record->direccion}"), // <--- MAGIA AQUÍ,
                Tables\Columns\TextColumn::make('asesor.name') // Accede al nombre del asesor a través de la relación
                    ->label('Asesor')
                    ->sortable()
                    ->searchable(),
                Tables\Columns\TextColumn::make('tipo_cliente')
                    ->badge() // Muestra el ENUM como una "badge" o etiqueta coloreada
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
                    'ninguno_registrado' => 'Ninguno Registrado', // Opcional
                ])
                ->query(function (Builder $query, array $data): Builder {
                    $value = $data['value']; // El valor de la opción seleccionada

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
                    ->relationship('barrio', 'nombre'), // Filtra por la relación
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
                ->url(null) // <--- AÑADE ESTO
                ->slideOver() // <--- ¡ESTA ES LA MAGIA PARA EL SLIDE-OVER!
                ->modalWidth('xl') // O 'lg', 'xl', etc. para el ancho del panel lateral
                ->modalHeading('Editar Contacto'), // Título opcional
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
            // Aquí podrías añadir RelationManagers si, por ejemplo, quisieras ver las órdenes de un contacto directamente.
            // RelationManagers\OrdenesRelationManager::class,
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