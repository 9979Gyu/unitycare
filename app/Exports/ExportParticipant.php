<?php

namespace App\Exports;

use App\Models\Program;
use App\Models\Participant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;


class ExportParticipant implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $selectedParticipants;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($selectedParticipants)
    {
        $this->selectedParticipants = $selectedParticipants;
    }

    public function collection()
    {
        if(isset($this->selectedParticipants)){
            $selectedParticipants = $this->selectedParticipants->map(function ($item) {

                return[
                    'Nama Pemohon' => $item->joined_username,
                    'Emel Pemohon' => $item->joined_useremail,
                    'Kategori' => $item->category,
                    'Jenis' => $item->typename,
                    'Tarikh Mohon' => $item->applied_date, 
                    'Nama Program' => $item->program_name, 
                    'Jenis Program' => $item->programtype,
                    'Nama Penganjur' => $item->program_creator_name,
                    'Emel Penganjur' => $item->program_creator_email,
                ];

            });
            
            return collect($selectedParticipants);
            
        }

    }

    public function headings(): array
    {
        return [
            'Nama Pemohon',
            'Emel Pemohon',
            'Kategori',
            'Jenis',
            'Tarikh Mohon',
            'Nama Program',
            'Jenis Program',
            'Nama Penganjur',
            'Emel Penganjur',
        ];
    }
}
