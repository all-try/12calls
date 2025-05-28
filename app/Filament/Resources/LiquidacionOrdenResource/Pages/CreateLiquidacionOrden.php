<?php

namespace App\Filament\Resources\LiquidacionOrdenResource\Pages;

use App\Filament\Resources\LiquidacionOrdenResource;
use App\Models\Orden; // Para actualizar el estado de la orden
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model; // Para el type hint de handleRecordCreation
use Filament\Notifications\Notification; // Para enviar notificaciones

class CreateLiquidacionOrden extends CreateRecord
{
    protected static string $resource = LiquidacionOrdenResource::class;

    // Para redirigir después de crear, por ejemplo, a la lista de liquidaciones
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    // Para personalizar el mensaje de notificación después de crear
    protected function getCreatedNotificationTitle(): ?string
    {
        return 'Liquidación de Orden Registrada';
    }

    // Aquí es donde recalculamos y preparamos los datos ANTES de que se cree el registro
    protected function mutateFormDataBeforeCreate(array $data): array
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

        // Asignar el técnico de la orden a la liquidación si no se seleccionó manualmente y la orden tiene uno
        // (Esto ya debería estar manejado por el afterStateUpdated en el form del Resource, pero es una doble verificación)
        if (empty($data['id_tecnico_liquida']) && !empty($data['id_orden'])) {
            $orden = Orden::find($data['id_orden']);
            if ($orden && $orden->id_tecnico) {
                $data['id_tecnico_liquida'] = $orden->id_tecnico;
            }
        }

        return $data;
    }

    // Después de que el registro de LiquidacionOrden se haya creado,
    // actualizamos el estado de la Orden principal.
    protected function afterCreate(): void
    {
        $liquidacion = $this->record; // El registro de LiquidacionOrden recién creado

        if ($liquidacion->id_orden) {
            $orden = Orden::find($liquidacion->id_orden);
            if ($orden) {
                // Cambia al estado que consideres apropiado después de crear la liquidación
                $orden->estado_orden = 'LIQUIDACION_EN_REVISION';
                $orden->save();
            }
        }
    }
}