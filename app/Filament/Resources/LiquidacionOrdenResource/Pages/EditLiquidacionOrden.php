<?php

namespace App\Filament\Resources\LiquidacionOrdenResource\Pages;

use App\Filament\Resources\LiquidacionOrdenResource;
use App\Models\Orden; // Para actualizar el estado de la orden si es necesario al editar
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model; // Para el type hint
use Filament\Notifications\Notification; // Para enviar notificaciones

class EditLiquidacionOrden extends EditRecord
{
    protected static string $resource = LiquidacionOrdenResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\ViewAction::make(), // Es bueno tener un botón para ir a la vista
            Actions\DeleteAction::make(),
        ];
    }

    // Para redirigir después de guardar, por ejemplo, a la lista o a la vista
    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
        // o para ir a la vista del registro editado:
        // return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }

    // Para personalizar el mensaje de notificación después de guardar
    protected function getSavedNotificationTitle(): ?string
    {
        return 'Liquidación de Orden Actualizada';
    }

    // Aquí es donde recalculamos y preparamos los datos ANTES de que se guarde el registro actualizado
    protected function mutateFormDataBeforeSave(array $data): array
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

        // No solemos cambiar la orden o el técnico en una liquidación existente,
        // así que no es necesaria la lógica de autocompletar id_tecnico_liquida aquí.
        // Los campos id_orden y id_tecnico_liquida deberían estar deshabilitados en el formulario de edición.

        return $data;
    }

    // Opcional: Después de que el registro de LiquidacionOrden se haya actualizado,
    // podrías querer actualizar el estado de la Orden principal si la lógica de negocio lo requiere.
    // Por ejemplo, si editar una liquidación la vuelve a poner en revisión.
    // protected function afterSave(): void
    // {
    //     $liquidacion = $this->record;

    //     if ($liquidacion->id_orden && $liquidacion->wasChanged('estado_liquidacion')) {
    //         $orden = Orden::find($liquidacion->id_orden);
    //         if ($orden) {
    //             if ($liquidacion->estado_liquidacion === 'ENTREGADA_PENDIENTE_REVISION') {
    //                  $orden->estado_orden = 'LIQUIDACION_EN_REVISION';
    //                  $orden->save();
    //             }
    //             // Añadir más lógica de estados si es necesario
    //         }
    //     }
    // }
}