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
            $programs = Program::where([
                ['status', 1],
                ['approved_status', 2],
            ])
            ->get();
        }
        else{
            $programs = Program::where([
                ['status', 1],
                ['approved_status', 2],
                ['user_id', Auth::user()->id]
            ])
            ->get();
        }
        return view('participants.index', compact('programs', 'roleNo'));
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
            $userID = Auth::user()->id;

            if(isset($selectedProgram)){

                if($selectedState == 0){
                    $selectedParticipants = Participant::join('programs', 'programs.program_id', '=', 'participants.program_id')
                    ->join('users', 'users.id', '=', 'participants.user_id')
                    ->join('poors', 'poors.user_id', '=', 'users.id')
                    ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                    ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                    ->where([
                        ['participants.status', $selectedState],
                        ['programs.status', 1],
                        ['programs.approved_status', 2],
                        ['programs.program_id', $selectedProgram]
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
                elseif($selectedState == 4){
                    $selectedParticipants = Participant::join('programs', 'programs.program_id', '=', 'participants.program_id')
                    ->join('users', 'users.id', '=', 'participants.user_id')
                    ->join('poors', 'poors.user_id', '=', 'users.id')
                    ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                    ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
                    ->where([
                        ['participants.status', 1],
                        ['programs.status', 1],
                        ['programs.approved_status', 2],
                        ['programs.program_id', $selectedProgram]
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
                        ['participants.user_type_id', $selectedState],
                        ['programs.program_id', $selectedProgram]
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
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            $state = $request->get('statusFilter');
            $selectedPosition = $request->get("program");
            $userID = Auth::user()->id;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            $filename = Participant::where('participant_id', $selectedPosition)->value('name');

            return Excel::download(new ExportParticipant(
                $roleID, $state, $selectedPosition, $userID, $startDate, $endDate), 
                'Peserta program (' . $filename . ') - ' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }
}
