<?php

namespace App\Exports;

use App\Models\Job;
use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportApplication implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $selectedApplication;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($selectedApplication)
    {
        $this->selectedApplication = $selectedApplication;
    }

    public function collection()
    {
        if(isset($this->selectedApplication)){
            $selectedApplication = $this->selectedApplication->map(function ($item) {
                return[
                    'Nama Pemohon' => $item->username,
                    'Emel Pemohon' => $item->useremail,
                    'Telefon Pemohon' => "(+60)" . $item->usercontact,
                    'Alamat' => $item->address,
                    'Peringkat Pendidikan' => $item->edu_level,
                    'Kategori' => $item->category,
                    'Sebab Mohon' => $item->description,
                    'Tarikh Mohon' => $item->applied_date, 
                    'Jawatan' => $item->position, 
                    'Status' => $item->approval,
                    'Diproses Oleh' => $item->processedname . " (" . $item->processedemail . ")", 
                    'Diproses Pada' => $item->approved_at, 
                ];
            });

            return $selectedApplication;
            
        }

    }

    public function headings(): array
    {
        return [
            'Nama Pemohon',
            'Emel Pemohon',
            'Telefon Pemohon',
            'Alamat',
            'Peringkat Pendidikan',
            'Kategori',
            'Sebab Mohon',
            'Tarikh Mohon',
            'Jawatan',
            'Status',
            'Diproses Oleh',
            'Diproses Pada',
        ];
    }
}
