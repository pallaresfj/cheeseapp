<?php

namespace App\Imports;

use App\Models\Branch;
use App\Models\Farm;
use App\Models\FarmType;
use App\Models\User;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Filament\Notifications\Notification;

class MyFarmImport implements ToCollection, WithHeadingRow
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        $created = 0;
        $skipped = 0;
        foreach ($rows as $row) 
        {
            // Convertir los valores de 'proveedor', 'sucursal' y 'tipo' a sus IDs correspondientes

            $userId = User::where('username', $row['proveedor'])->value('id');
            $branchId = Branch::where('name', $row['sucursal'])->value('id');
            $farmTypeId = FarmType::where('name', $row['tipo'])->value('id');

            if (Farm::where('user_id', $userId)
                    ->where('branch_id', $branchId)
                    ->where('name', $row['finca'])
                    ->exists()) {
                $skipped++;
                continue;
            }

            Farm::create([
                'user_id' => $userId,
                'name' => $row['finca'],
                'branch_id' => $branchId,
                'farm_type_id' => $farmTypeId,
            ]);
            $created++;
        }
        Notification::make()
            ->title('Importación finalizada')
            ->body("Se importaron {$created} fincas. No se importaron {$skipped} por conflictos de datos.")
            ->success()
            ->send();
    }
}
