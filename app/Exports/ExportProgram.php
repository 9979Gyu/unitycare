<?php

namespace App\Exports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportProgram implements FromCollection, withHeadings, ShouldAutoSize
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
            $selectedPrograms = $this->selectedPrograms->map(function ($program) {
                return[
                    'Nama' => $program->name,
                    'Jenis' => $program->typename,
                    'Penerangan' => $program->description,
                    'Lokasi' => $program->address,
                    'Tarikh Mula' => $program->start,
                    'Tarikh Tamat' => $program->end,
                    'Peserta' => $program->vol . ', ' . $program->poor,
                    'Tarikh Tutup Permohonan' => $program->close_date,
                    'Nama Penganjur' => $program->username,
                    'Emel Penganjur' => $program->useremail,
                    'Nombor Telefon Penganjur' => '(+60)' . $program->usercontact,
                ];
            });
        }

        return $selectedPrograms;

    }

    public function headings(): array
    {
        return [
            "Program",
            "Jenis",
            "Penerangan",
            "Lokasi",
            "Tarikh Mula",
            "Tarikh Tamat",
            "Peserta",
            "Tarikh Tutup Permohonan",
            'Nama Penganjur',
            'Emel Penganjur',
            'Nombor Telefon Penganjur',
        ];
    }
}
