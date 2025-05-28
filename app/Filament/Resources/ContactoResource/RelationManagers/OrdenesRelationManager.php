<?php

namespace App\Filament\Resources\ContactoResource\RelationManagers;

use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrdenesRelationManager extends RelationManager
{
    protected static string $relationship = 'ordenes';

    protected static ?string $title = 'Órdenes del Cliente';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('numero_orden')
                    ->label('Número de Orden')
                    ->required()
                    ->maxLength(50),
                
                Forms\Components\Select::make('estado')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'EN_PROCESO' => 'En Proceso',
                        'COMPLETADA' => 'Completada',
                        'CANCELADA' => 'Cancelada',
                    ])
                    ->required(),
                
                Forms\Components\DatePicker::make('fecha_orden')
                    ->label('Fecha de Orden')
                    ->required(),
                
                Forms\Components\TextInput::make('total')
                    ->numeric()
                    ->prefix('$')
                    ->required(),
                
                Forms\Components\Textarea::make('observaciones')
                    ->maxLength(500)
                    ->columnSpanFull(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('numero_orden')
            ->columns([
                Tables\Columns\TextColumn::make('numero_orden')
                    ->label('# Orden')
                    ->searchable()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('fecha_orden')
                    ->label('Fecha')
                    ->date()
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('estado')
                    ->badge()
                    ->color(fn (string $state): string => match ($state) {
                        'COMPLETADA' => 'success',
                        'EN_PROCESO' => 'warning',
                        'PENDIENTE' => 'info',
                        'CANCELADA' => 'danger',
                        default => 'gray',
                    }),
                
                Tables\Columns\TextColumn::make('total')
                    ->money('COP')
                    ->sortable(),
                
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Creada')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('estado')
                    ->options([
                        'PENDIENTE' => 'Pendiente',
                        'EN_PROCESO' => 'En Proceso',
                        'COMPLETADA' => 'Completada',
                        'CANCELADA' => 'Cancelada',
                    ]),
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make()
                    ->label('Nueva Orden'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}