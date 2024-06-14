<?php

namespace App\Exports;

use App\Models\Program;
use App\Models\Participant;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportParticipant implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $id;
    private $state;
    private $selectedPosition;
    private $userID;
    private $startDate;
    private $endDate;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id, $state, $selectedPosition, $userID, $startDate, $endDate)
    {
        $this->id = $id;
        $this->state = $state;
        $this->selectedPosition = $selectedPosition;
        $this->userID = $userID;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
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
        if(isset($this->selectedPosition)){

            if($this->state == 0){
                $selectedParticipants = Participant::join('programs', 'programs.program_id', '=', 'participants.program_id')
                ->join('users', 'users.id', '=', 'participants.user_id')
                ->join('poors', 'poors.user_id', '=', 'users.id')
                ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                ->where([
                    ['participants.status', $this->state],
                    ['programs.status', 1],
                    ['programs.approved_status', 2],
                    ['programs.program_id', $this->selectedPosition],
                    ['participants.created_at', '>=', $this->startDate],
                    ['participants.created_at', '<=', $this->endDate],
                ])
                ->select(
                    'participants.*',
                    'users.name as username',
                    'users.email as useremail',
                    'users.contactNo as usercontact',
                    'poors.disability_type',
                    'dt.name as category',
                    'programs.name as name',
                    'ut.name as typename',
                )
                ->orderBy("participants.created_at", "asc")
                ->get();
            }
            elseif($this->state == 4){
                $selectedParticipants = Participant::join('programs', 'programs.program_id', '=', 'participants.program_id')
                ->join('users', 'users.id', '=', 'participants.user_id')
                ->join('poors', 'poors.user_id', '=', 'users.id')
                ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                ->where([
                    ['participants.status', 1],
                    ['programs.status', 1],
                    ['programs.approved_status', 2],
                    ['programs.program_id', $this->selectedPosition],
                    ['participants.created_at', '>=', $this->startDate],
                    ['participants.created_at', '<=', $this->endDate],
                ])
                ->select(
                    'participants.*',
                    'users.name as username',
                    'users.email as useremail',
                    'users.contactNo as usercontact',
                    'poors.disability_type',
                    'dt.name as category',
                    'programs.name as name',
                    'ut.name as typename',
                )
                ->orderBy("participants.created_at", "asc")
                ->get();
            }
            else{
                $selectedParticipants = Participant::join('programs', 'programs.program_id', '=', 'participants.program_id')
                ->join('users', 'users.id', '=', 'participants.user_id')
                ->join('poors', 'poors.user_id', '=', 'users.id')
                ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                ->where([
                    ['participants.status', 1],
                    ['programs.status', 1],
                    ['programs.approved_status', 2],
                    // ['programs.user_id', $userID],
                    ['participants.user_type_id', $this->state],
                    ['programs.program_id', $this->selectedPosition],
                    ['participants.created_at', '>=', $this->startDate],
                    ['participants.created_at', '<=', $this->endDate],
                ])
                ->select(
                    'participants.*',
                    'users.name as username',
                    'users.email as useremail',
                    'users.contactNo as usercontact',
                    'poors.disability_type',
                    'dt.name as category',
                    'programs.name as name',
                    'ut.name as typename',
                )
                ->orderBy("participants.created_at", "asc")
                ->get();
            }

            dd($selectedParticipants);

            if(isset($selectedParticipants)){
                $selectedParticipants = $selectedParticipants->map(function ($item) {

                    return[
                        'Nama Pemohon' => $item->username,
                        'Emel Pemohon' => $item->useremail,
                        'Telefon Nombor Pemohon' => $item->usercontact,
                        'Kategori' => $item->disability_type,
                        'Jenis' => $item->typename,
                        'Tarikh Mohon' => parseDate($item->created_at), 
                        'Program' => $item->name, 
                    ];
                });

            }
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
        ];
    }
}
