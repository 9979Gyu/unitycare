<?php

namespace App\Exports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;


class ExportProgramReport implements FromCollection, withHeadings, ShouldAutoSize
{
    // Declare varible
    private $selectedPrograms;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($selectedPrograms)
    {
        $this->selectedPrograms = $selectedPrograms;
    }

    public function collection()
    {
        if(isset($this->selectedPrograms)){
            $selectedPrograms = $this->selectedPrograms->map(function ($item) {

                $usercontact = '(+60)' . $item->usercontact;

                if($item->approved_status == 0){
                    $approval = "Ditolak";
                }
                elseif($item->approved_status == 1){
                    $approval = "Belum Diproses";
                }
                else{
                    $approval = "Telah Diluluskan";
                }

                if($item->processedname == null){
                    $processed = " ";
                }
                else{
                    $processed = $item->processedname . ' (' . $item->processedemail . ")";
                }

                return[
                    'Program' => $item->name,
                    'Jenis' => $item->typename,
                    "Penerangan" => $item->description,
                    "Tempat" => $item->address,
                    "Tarikh Mula" => $item->start,
                    "Tarikh Tamat" => $item->end,
                    "Sukarelawan" => $item->vol,
                    "B40 / OKU" => $item->poor,
                    "Tarikh Tutup Permohonan" => $item->close_date,
                    'Nama Penganjur' => $item->username,
                    'Emel Penganjur' => $item->useremail,
                    'Telefon Nombor Penganjur' => $usercontact,
                    "Status" => $approval,
                    "Diproses oleh" => $processed,
                    "Diproses pada" => $item->approved_at
                ];

            });
            return collect($selectedPrograms);
            
        }

    }

    public function headings(): array
    {
        return [
            "Program",
            "Jenis",
            "Penerangan",
            "Tempat",
            "Tarikh Mula",
            "Tarikh Tamat",
            "Sukarelawan",
            "B40 / OKU",
            "Tarikh Tutup Permohonan",
            'Nama Penganjur',
            'Emel Penganjur',
            'Telefon Nombor Penganjur',
            "Status",
            "Diproses oleh",
            "Diproses pada",
        ];
    }
}
