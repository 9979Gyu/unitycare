<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Program;
use App\Models\Program_Spec;
use App\Models\Participant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportParticipant;
use Maatwebsite\Excel\Facades\Excel;

class ParticipantController1 extends Controller
{
    //
    public function index(){
        $roleNo = Auth::user()->roleID;
        if($roleNo == 1 || $roleNo == 2){
            $users = User::where([
                ['users.status', 1],
            ])
            ->join('programs', 'programs.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
            )
            ->orderBy('users.name')
            ->distinct()
            ->get();
        }
        else{
            $users = User::where([
                ['status', 1],
                ['id', Auth::user()->id]
            ])
            ->select(
                'users.id',
                'users.name',
            )
            ->orderBy('users.name')
            ->distinct()
            ->get();
        }

        return view('participants.index', compact('roleNo', 'users'));
    }

    public function getParticipantsDatatable(Request $request)
    {
        if(request()->ajax()){
            $rid = $request->get("rid");
            $selectedState = $request->get('selectedState');
            $selectedProgram = $request->get("programID");
            $selectedOrganization = $request->get("userID");

            if(isset($selectedProgram)){
                if($selectedState == 0 || $selectedState == 1){
                    $selectedParticipants = Participant::where('participants.status', $selectedState)
                    ->join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
                    ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                    ->join('programs', 'programs.program_id', '=', 'participants.program_id')
                    ->join('users as program_creator', 'program_creator.id', '=', 'programs.user_id')
                    ->where([
                        ['programs.status', 1],
                        ['programs.approved_status', 2],
                        ['programs.program_id', $selectedProgram],
                        ['programs.user_id', $selectedOrganization]
                    ]);

                }
                else{
                    $selectedParticipants = Participant::where('participants.status', 1)
                    ->join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
                    ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                    ->join('programs', 'programs.program_id', '=', 'participants.program_id')
                    ->join('users as program_creator', 'program_creator.id', '=', 'programs.user_id')
                    ->where([
                        ['programs.status', 1],
                        ['programs.approved_status', 2],
                        ['programs.program_id', $selectedProgram],
                        ['programs.user_id', $selectedOrganization],
                        ['participants.user_type_id', $selectedState], 
                    ]);
                }

                $selectedParticipants = $selectedParticipants->select(
                    'participants.*',
                    'joined_users.name as joined_username',
                    'joined_users.email as joined_useremail',
                    'joined_users.contactNo as joined_usercontact',
                    'poors.disability_type',
                    'dt.name as category',
                    'programs.name as program_name',
                    'ut.name as typename',
                    'program_creator.name as program_creator_name',
                    'program_creator.email as program_creator_email',
                    'program_creator.contactNo as program_creator_contact'
                )
                ->leftJoin('poors', function ($join) {
                    $join->on('poors.user_id', '=', 'joined_users.id');
                })
                ->leftJoin('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                ->orderBy("participants.created_at", "asc")
                ->get();
            }

            if(isset($selectedParticipants)){

                $table = Datatables::of($selectedParticipants);
                return $table->make(true);
            }

        }

        return redirect('/indexparticipant');
    }

    // Function to export programs info
    public function exportParticipants(Request $request){
        
        // Validate the request data
        $rules = [
            'roleID' => 'required',
            'statusFilter' => 'required',
            'program' => 'required',
            'organization' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            $selectedState = $request->get('statusFilter');
            $selectedProgram = $request->get("program");
            $selectedOrganization = $request->get("organization");
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if(isset($selectedProgram)){
                if($selectedState == 0 || $selectedState == 1){
                    $selectedParticipants = Participant::where([
                        ['participants.status', $selectedState],
                        ['participants.created_at', '>=', $startDate],
                        ['participants.created_at', '<=', $endDate],
                    ])
                    ->join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
                    ->join('poors', 'poors.user_id', '=', 'joined_users.id')
                    ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                    ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                    ->join('programs', 'programs.program_id', '=', 'participants.program_id')
                    ->where([
                        ['programs.status', 1],
                        ['programs.approved_status', 2],
                        ['programs.program_id', $selectedProgram],
                        ['programs.user_id', $selectedOrganization],
                    ])
                    ->join('users as program_creator', 'program_creator.id', '=', 'programs.user_id')
                    ->select(
                        'participants.*',
                        'joined_users.name as joined_username',
                        'joined_users.email as joined_useremail',
                        'joined_users.contactNo as joined_usercontact',
                        'poors.disability_type',
                        'dt.name as category',
                        'programs.name as program_name',
                        'ut.name as typename',
                        'program_creator.name as program_creator_name',
                        'program_creator.email as program_creator_email',
                        'program_creator.contactNo as program_creator_contact'
                    )
                    ->orderBy("participants.created_at", "asc")
                    ->get();

                }
                else{
                    $selectedParticipants = Participant::where([
                        ['participants.status', 1],
                        ['participants.created_at', '>=', $startDate],
                        ['participants.created_at', '<=', $endDate],
                    ])
                    ->join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
                    ->join('poors', 'poors.user_id', '=', 'joined_users.id')
                    ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                    ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                    ->join('programs', 'programs.program_id', '=', 'participants.program_id')
                    ->where([
                        ['programs.status', 1],
                        ['programs.approved_status', 2],
                        ['programs.program_id', $selectedProgram],
                        ['programs.user_id', $selectedOrganization],
                        ['participants.user_type_id', $selectedState],                        
                    ])
                    ->join('users as program_creator', 'program_creator.id', '=', 'programs.user_id')
                    ->select(
                        'participants.*',
                        'joined_users.name as joined_username',
                        'joined_users.email as joined_useremail',
                        'joined_users.contactNo as joined_usercontact',
                        'poors.disability_type',
                        'dt.name as category',
                        'programs.name as program_name',
                        'ut.name as typename',
                        'program_creator.name as program_creator_name',
                        'program_creator.email as program_creator_email',
                        'program_creator.contactNo as program_creator_contact'
                    )
                    ->orderBy("participants.created_at", "asc")
                    ->get();

                }
            }

            $filename = Program::where('program_id', $selectedProgram)->value('name');

            return Excel::download(new ExportParticipant($selectedParticipants), 
                'Peserta program (' . $filename . ') - ' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }
}
