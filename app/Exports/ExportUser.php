<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportUser implements FromCollection, withHeadings, ShouldAutoSize
{

    // Declare varible
    private $id;
    private $isB40;
    
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id)
    {
        $this->id = $id;
        $this->isB40 = ($this->id == 5);
    }

    public function collection()
    {
        // Get list of user based on role id
        if($this->isB40){
            $users = User::where([
                ['roleID', $this->id],
                ['status', 1],
            ])
            ->with(['poor.disabilityType'])
            ->orderBy('name')
            ->get();

            // Map user data and disability type name
            $users = $users->map(function ($user) {
                return[
                    'name' => $user->name,
                    'ICNo' => $user->ICNo,
                    'username' => $user->username,
                    'disTypeName' =>$user->poor ? $user->poor->disabilityType->name : null,
                    'email' => $user->email,
                    'contactNo' => $user->contactNo,
                    'state' => $user->state,
                    'city' => $user->city,
                    'postalCode' => $user->postalCode,
                    'address' => $user->address,
                ];
            });
        }
        else{
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
        }
        
        return $users;
    }

    public function headings(): array
    {
        if($this->isB40){
            return [
                'Nama',
                'Nombor Pengenalan',
                'Nama Pengguna',
                'Kategori Kecacatan',
                'Emel',
                'Nombor Telefon',
                'Negeri',
                'Bandar',
                'Poskod',
                'Alamat',
            ];
        }
        else{
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
}
