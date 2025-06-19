<?php

namespace App\Imports;

use App\Models\MilkPurchase;
use Filament\Notifications\Notification;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\ToCollection;

class MyMilkPurchaseImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $imported = 0;
        $skipped = 0;

        foreach ($rows as $row) 
        {
            $exists = MilkPurchase::where('farm_id', $row['finca'])
                ->where('date', $row['fecha'])
                ->exists();

            if (! $exists) {
                MilkPurchase::create([
                    'date' => $row['fecha'],
                    'branch_id' => $row['sucursal'],
                    'farm_id' => $row['finca'],
                    'liters' => $row['litros'],
                    'status' => $row['status'],
                ]);
                $imported++;
            } else {
                $skipped++;
            }
        }
        Notification::make()
            ->title('Importación finalizada')
            ->body("Se importaron {$imported} compras. No se importaron {$skipped} por conflictos de datos.")
            ->success()
            ->send();

        
    }
}
