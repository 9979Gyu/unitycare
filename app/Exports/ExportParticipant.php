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

    public function parseDate($olddate){
        try {

            // Parse the date with the specified format
            $date = Carbon::createFromFormat('Y-m-d', $olddate);

            // Set the locale to Malay
            $date->locale('ms');

            // Format the date, e.g., 'dddd, D MMMM YYYY'
            $formattedDate = $date->isoFormat('dddd, D MMMM YYYY');

            return $formattedDate;

        } 
        catch (Exception $e) {
            return $date;
        }
    }

    public function collection()
    {
        if(isset($this->selectedParticipants)){
            $selectedParticipants = $this->selectedParticipants->map(function ($item) {
                
                $olddate = explode(' ', $item->created_at);
                $datetime = $this->parseDate($olddate[0]) . ' ' . $olddate[1];

                $usercontact = '(+60)' . $item->joined_usercontact;
                $creatorcontact = '(+60)' . $item->program_creator_contact;

                return[
                    'Nama Pemohon' => $item->joined_username,
                    'Emel Pemohon' => $item->joined_useremail,
                    'Telefon Nombor Pemohon' => $usercontact,
                    'Kategori' => $item->category,
                    'Jenis' => $item->typename,
                    'Tarikh Mohon' => $datetime, 
                    'Program' => $item->program_name, 
                    'Nama Penganjur' => $item->program_creator_name,
                    'Emel Penganjur' => $item->program_creator_email,
                    'Telefon Nombor Penganjur' => $creatorcontact,
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
            'Telefon Nombor Pemohon',
            'Kategori',
            'Jenis',
            'Tarikh Mohon',
            'Program',
            'Nama Penganjur',
            'Emel Penganjur',
            'Telefon Nombor Penganjur',
        ];
    }
}
