<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportApplied implements FromCollection, WithHeadings, ShouldAutoSize
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
        //
        if(isset($this->selectedApplication)){

            $selectedApplication = $this->selectedApplication->map(function ($item) {

                return[
                    'Pekerjaan' => $item->jobname . ' - ' . $item->jobposition,
                    'Jenis' => $item->typename,
                    'Syif' => $item->shiftname,
                    'Lokasi'  => $item->address,
                    'Tarikh' => $item->start,
                    'Masa' => $item->end,
                    'Purata Gaji' => 'RM ' . $item->min_salary . ' - RM ' . $item->max_salary,
                    'Sebab Mohon' => $item->description,
                    'Tarikh Mohon' => $item->applied_date,
                    'Nama Pengurus' => $item->username,
                    'Emel Pengurus' => $item->useremail,
                    'Telefon Pengurus' => '(+60)' . $item->usercontact,
                    'Status' => $item->approval,
                    'Diproses Pada' => $item->approved_at,
                ];
            });
    
            return $selectedApplication;
        }
    }

    public function headings(): array{
        return [
            'Pekerjaan',
            'Jenis',
            'Syif',
            'Lokasi',
            'Tarikh',
            'Masa',
            'Purata Gaji',
            'Sebab Mohon',
            'Tarikh Mohon',
            'Nama Pengurus',
            'Emel Pengurus',
            'Telefon Pengurus',
            'Status',
            'Diproses Pada'
        ];
    }
}
