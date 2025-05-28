<?php

namespace App\Filament\Resources\LiquidacionOrdenResource\Pages;

use App\Filament\Resources\LiquidacionOrdenResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListLiquidacionOrdenes extends ListRecords
{
    protected static string $resource = LiquidacionOrdenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
