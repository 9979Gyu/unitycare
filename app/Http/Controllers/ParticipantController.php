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

class ParticipantController extends Controller
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
            ->get();
        }

        return view('participants.index', compact('roleNo', 'users'));
    }

    public function create($id){

        if(Auth::check()){
            $program = Program::with('organization')
            ->where([
                ['program_id', $id],
                ['status', 1],
                ['approved_status', 2]
            ])
            ->select(
                '*',
                'description->desc as description',
                'description->reason as reason'
            )
            ->first();

            $volLimit = Program::where([
                ['program_id', $id],
                ['status', 1],
                ['approved_status', 2],
            ])
            ->with(['programSpecs' => function ($query) {
                $query->where('user_type_id', 2);
            }])
            ->first();       

            $poorLimit = Program::where([
                ['program_id', $id],
                ['status', 1],
                ['approved_status', 2],
            ])
            ->with(['programSpecs' => function ($query) {
                $query->where('user_type_id', 3);
            }])
            ->first();

            $participantExist = Participant::where([
                ['status', 1],
                ['user_id', Auth::user()->id]
            ])
            ->with(['programs' => function ($query) {
                $query->where('program_id', $id);
            }])
            ->count();

            $volRemain = $volLimit->programSpecs[0]->qty_limit - $volLimit->programSpecs[0]->qty_enrolled;
            $poorRemain = $poorLimit->programSpecs[0]->qty_limit - $poorLimit->programSpecs[0]->qty_enrolled;

            return view('participants.add', compact('program', 'volLimit', 'poorLimit', 'volRemain', 'poorRemain', 'participantExist'));
            
        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk untuk melayari halaman']);

    }

    public function store(Request $request){
    
        $userType = $request->get("button_id");
        $programID = $request->get("program_id");
        $uid = Auth::user()->id;

        if(isset($userType) && isset($programID)){
            $participant = new participant([
                'user_type_id' => $userType,
                'program_id' => $programID,
                'user_id' => $uid,
                'status' => 1,
            ]);

            $result = $participant->save();

            if($result){
                $updateEnrolled = DB::table('program_specs')
                ->where([
                    ['program_id', $programID],
                    ['user_type_id', $userType],
                ])
                ->increment('qty_enrolled', 1);

                return redirect('/viewprogram')->with('success', 'Berjaya didaftarkan');
            }
            else{
                return redirect('/viewprogram')->withErrors(['message' => "Pendaftaran tidak berjaya"]);
            }
        }
    }

    public function dismiss(Request $request)
    {
        //
        $update = DB::table('program_specs')
        ->join('participants as p', 'p.user_type_id', '=', 'program_specs.user_type_id')
        ->where([
            ['p.user_id', Auth::user()->id],
            ['p.status', 1],
            ['program_specs.program_id', $request->selectedID],
            ['p.program_id', $request->selectedID],
        ])
        ->decrement('program_specs.qty_enrolled', 1);

        if($update){

            $result = DB::table('participants')
            ->where([
                ['status', 1],
                ['program_id', $request->selectedID],
            ])
            ->update([
                'status' => 0,
            ]);    

            if($result)
                return redirect()->back()->with('success', 'Berjaya dipadam');
        }

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
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
