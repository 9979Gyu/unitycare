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

    public function parseDate($olddate){
        try {
            // Parse the date with the specified format
            $date = Carbon::createFromFormat('Y-m-d', $olddate);

            // Set the locale to Malay
            $date->locale('ms');

            // Format the date to 'dddd, D MMMM YYYY' (without time since it's not provided)
            $formattedDate = $date->isoFormat('dddd, D MMMM YYYY');

            return $formattedDate;

        } 
        catch (Exception $e) {
            return $date;
        }
    }

    public function collection()
    {
        if(isset($this->selectedPrograms)){
            $selectedPrograms = $this->selectedPrograms->map(function ($item) {

                $usercontact = '(+60)' . $item->usercontact;

                if($item->user_type_id == 2){
                    $participant = $item->participant . ' (Sukarelawan)';
                }
                else if($item->user_type_id == 3){
                    $participant = $item->participant . ' (B40/OKU)';
                }

                if($item->approved_status == 0){
                    $approval = "Ditolak";
                }
                elseif($item->approved_status == 1){
                    $approval = "Belum Diproses";
                }
                else{
                    $approval = "Telah Diluluskan";
                }

                return[
                    'Program' => $item->name,
                    'Jenis' => $item->typename,
                    "Penerangan" => $item->description,
                    "Tempat" => $item->address,
                    "Tarikh Mula" => $item->start,
                    "Tarikh Tamat" => $item->end,
                    "Peserta" => $participant,
                    "Tarikh Tutup Permohonan" => $item->close_date,
                    'Nama Penganjur' => $item->username,
                    'Emel Penganjur' => $item->useremail,
                    'Telefon Nombor Penganjur' => $usercontact,
                    "Status" => $approval,
                    "Diproses oleh" => $item->processedemail,
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
            "Peserta",
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
