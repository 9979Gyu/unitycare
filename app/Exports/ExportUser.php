<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportUser implements FromCollection, withHeadings
{
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id)
    {
        $this->id = $id;
    }

    public function collection()
    {
        // Get list of user based on role id
        $users = User::where([
            ['roleID', $this->id],
            ['status', 1],
        ])
        ->select(
            'name',
            'ICNo',
            'username',
            'email',
            'contactNo',
            'state',
            'city',
            'postalCode',
            'address',
        )
        ->orderBy('name')
        ->get();

        return $users;
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Nombor Pengenalan',
            'Nama Pengguna',
            'Emel',
            'Nombor Telefon',
            'Negeri',
            'Bandar',
            'Poskod',
            'Alamat',
        ];
    }
}
