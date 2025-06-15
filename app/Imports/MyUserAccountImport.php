<?php

namespace App\Imports;

use App\Models\UserAccount;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Hash;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

use EightyNine\ExcelImport\EnhancedDefaultImport;

class MyUserAccountImport implements ToCollection, WithHeadingRow 
{
    /**
    * @param Collection $collection
    */
    public function collection(Collection $rows)
    {
        foreach ($rows as $row) 
        {
            UserAccount::create([
                'name' => $row['nombre'],
                'email' => $row['correo'],
                'role' => $row['rol'],
                'username' => $row['usuario'],
                'password' => Hash::make('pas123'), // Default password
            ]);
        }
    }
}
