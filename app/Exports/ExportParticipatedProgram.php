<?php

namespace App\Exports;

use App\Models\Program;
use App\Models\Participant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;


class ExportParticipatedProgram implements FromCollection, WithHeadings, ShouldAutoSize
{
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

                return[
                    'Nama Program' => $item->program_name,
                    'Jenis Program' => $item->typename,
                    'Penerangan' => $item->description,
                    'Lokasi' => $item->address,
                    'Tarikh Mula' => $item->start,
                    'Tarikh Tamat' => $item->end,
                    'Nama Penganjur' => $item->creator_name,
                    'Emel Penganjur' => $item->creator_email,
                    'Tarikh Mohon' => $item->applied_date,
                ];

            });
            
            return collect($selectedPrograms);
            
        }

    }

    public function headings(): array
    {
        return [
            'Nama Program',
            'Jenis Program',
            'Penerangan',
            'Lokasi',
            'Tarikh Mula',
            'Tarikh Tamat',
            'Nama Penganjur',
            'Emel Penganjur',
            'Tarikh Mohon',
        ];
    }
}
