<?php

namespace App\Filament\Resources\LiquidacionOrdenResource\Pages;

use App\Filament\Resources\LiquidacionOrdenResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewLiquidacionOrden extends ViewRecord
{
    protected static string $resource = LiquidacionOrdenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(), // Para poder editar desde la vista
        ];
    }
}