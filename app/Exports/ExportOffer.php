<?php

namespace App\Exports;

use App\Models\Job_Offer;
use App\Models\Job;
use App\Models\Shift_Type;
use App\Models\Job_Type;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportOffer implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $selectedOffers;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($selectedOffers){
        $this->selectedOffers = $selectedOffers;
    }

    public function collection()
    {
        if(isset($this->selectedOffers)){

            $selectedOffers = $this->selectedOffers->map(function ($item) {

                return[
                    'Pekerjaan' => $item->jobname . ' - ' . $item->jobposition,
                    'Jenis' => $item->typename,
                    'Syif' => $item->shiftname,
                    'Lokasi'  => $item->address,
                    'Tarikh' => $item->start,
                    'Masa' => $item->end,
                    'Purata Gaji' => 'RM ' . $item->min_salary . ' - RM ' . $item->max_salary,
                    'Pekerja Diperlukan' => $item->quantity,
                    'Penerangan' => $item->description,
                    'Nama Pengurus' => $item->username,
                    'Emel Pengurus' => $item->useremail,
                    'Telefon Pengurus' => '(+60)' . $item->usercontact,
                    'Status' => $item->approval,
                    'Diproses Oleh' => $item->processedname . ' (' . $item->processedemail . ')',
                    'Diproses Pada' => $item->approved_at,
                ];
            });
    
            return $selectedOffers;
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
            'Pekerja Diperlukan',
            'Penerangan',
            'Nama Pengurus',
            'Emel Pengurus',
            'Telefon Pengurus',
            'Status',
            'Diproses Oleh',
            'Diproses Pada'
        ];
    }
}
