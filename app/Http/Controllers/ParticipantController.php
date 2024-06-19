<?php

namespace App\Http\Controllers;

use App\Mail\NotifyParticipant;
use App\Models\Program;
use App\Models\Program_Spec;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportParticipant;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use DB;

class ParticipantController extends Controller
{

    // Function to display list of program or participants 
    public function index(){
        if(Auth::check()){

            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            if($roleID != 1 && $roleID != 2){
                $users = User::where([
                    ['status', 1],
                    ['id', $userID],
                ])
                ->select('id', 'name')
                ->groupBy('id', 'name')
                ->orderBy('name')
                ->get();
            }
            else{
                $users = User::where([
                    ['users.status', 1],
                ])
                ->join('program as p', 'p.user_id', '=', 'users.id')
                ->select('users.id', 'users.name')
                ->groupBy('users.id', 'users.name')
                ->orderBy('users.name')
                ->get();
            }

            return view('participants.index', compact('users', 'roleID'));

        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    // Function to display create participants view
    public function create($id){

        if(Auth::check()){

            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            $program = Program::with('organization')
            ->where([
                ['program_id', $id],
                ['status', 1],
            ])
            ->select(
                '*',
                'description->desc as description',
                'description->reason as reason'
            )
            ->first();

            $program->start_date = DateController::parseDate($program->start_date);
            $program->start_time = DateController::formatTime($program->start_time);
            $program->end_date = DateController::parseDate($program->end_date);
            $program->end_time = DateController::formatTime($program->end_time);
            $program->close_date = DateController::parseDate($program->close_date);

            $volLimit = Program::where([
                ['program_id', $id],
            ])
            ->with(['programSpecs' => function ($query) {
                $query->where('user_type_id', 2);
            }])
            ->first();       

            $poorLimit = Program::where([
                ['program_id', $id],
            ])
            ->with(['programSpecs' => function ($query) {
                $query->where('user_type_id', 3);
            }])
            ->first();

            $participantExist = Participant::where([
                ['user_id', $userID],
                ['status', 1]
            ])
            ->with(['programs' => function ($query) {
                $query->where('program_id', $id);
            }])
            ->count();

            $volRemain = $volLimit->programSpecs[0]->qty_limit - $volLimit->programSpecs[0]->qty_enrolled;
            $poorRemain = $poorLimit->programSpecs[0]->qty_limit - $poorLimit->programSpecs[0]->qty_enrolled;

            return view('participants.add', compact('program', 'volLimit', 'poorLimit', 'volRemain', 'poorRemain', 'participantExist', 'roleID', 'userID'));
            
        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);
    }

    // Function to store participants details
    public function store(Request $request){

        $programID = $request->get('programID');
        $userType = $request->get("button_id");
        $userID = Auth::user()->id;

        if(isset($userType) && isset($programID)){
            $participant = new participant([
                'user_type_id' => $userType,
                'program_id' => $programID,
                'user_id' => $userID,
                'status' => 1,
            ]);

            $result = $participant->save();

            if($result){
                $updateEnrolled = Program_Spec::where([
                    ['program_id', $programID],
                    ['user_type_id', $userType],
                ])
                ->increment('qty_enrolled', 1);

                $this->notifyUser($programID, $userID, 1);

                return redirect('/viewallprograms')->with('success', 'Berjaya didaftarkan');
            }

        }

        return redirect('/viewallprograms')->withErrors(['message' => "Pendaftaran tidak berjaya"]);

    }

    // Function to remove user from participant
    public function dismiss(Request $request){
        //
        $participantID = $request->get('selectedID');

        $update = 0;

        if(isset($participantID)){

            $type = Participant::where([
                ['participants.status', 1],
                ['participants.participant_id', $participantID],
            ])
            ->select("user_type_id", 'program_id', 'user_id')
            ->first();

            $update = Participant::where([
                ['participants.status', 1],
                ['participants.participant_id', $participantID],
            ])
            ->join('program_specs as ps', 'ps.program_id', '=', 'participants.program_id')
            ->where('ps.user_type_id', $type->user_type_id)
            ->update([
                'ps.qty_enrolled' => DB::raw('ps.qty_enrolled - 1'),
                'participants.status' => 0,
            ]);
        }

        if($update){

            $this->notifyUser($type->program_id, $type->user_id, 0);

            return redirect()->back()->with('success', 'Berjaya dipadam');
        }

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
    }

    // Function to retrieve participants
    public function retrieveParticipants($state, $userID, $prorgamID, $startDate, $endDate){

        if(isset($programID)){

            $query = Participant::join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
            ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
            ->join('programs as p', 'p.program_id', '=', 'participants.program_id')
            ->join('users as program_creator', 'program_creator.id', '=', 'programs.user_id')
            ->where([
                ['p.status', 1],
                ['p.approved_status', 2],
            ]);

            if($startDate != null && $endDate != null){
                $query->where([
                    ['participants.created_at', '>=', $startDate],
                    ['participants.created_at', '<=', $endDate],
                ]);
            }

            if($userID != "all"){
                $query = $query->where('p.user_id', $userID);
            }

            if($programID != "all"){
                $query = $query->where('p.program_id', $programID);
            }

            if($state == 0 || $state == 1){
                $query = $query->where('participants.status', $state);
            }
            else{
                $query = $query->where([
                    ['participants.status', 1],
                    ['participants.user_type_id', $state], 
                ]);
            }

            $selectedParticipants = $query->select(
                'participants.created_at',
                'participants.participant_id',
                'joined_users.id as userID',
                'joined_users.name as joined_username',
                'joined_users.email as joined_useremail',
                'dt.name as category',
                'p.name as program_name',
                'p.program_id',
                'ut.name as typename',
                'program_creator.name as program_creator_name',
                'program_creator.email as program_creator_email',
            )
            ->leftJoin('poors', function ($join) {
                $join->on('poors.user_id', '=', 'joined_users.id');
            })
            ->leftJoin('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
            ->orderBy("participants.created_at", "asc")
            ->get();
        }

        // Transform the data but keep it as a collection of objects
        $selectedParticipants->transform(function ($participant) {

            if($participant->created_at != null){
                $created_at = explode(' ', $participant->created_at);
                $participant->created_at = DateController::parseDate($created_at[0]) . ' ' . DateController::formatTime($created_at[1]);
            }

            return $participant;
        });

        return $selectedParticipants;
    }

    // Function to display list of participant
    public function getParticipantsDatatable(Request $request)
    {
        if(request()->ajax()){
            $state = $request->get('state');
            $programID = $request->get("programID");
            $userID = $request->get("userID");
            $startDate = $request->get("startDate");
            $endDate = $request->get("endDate");
            
            if(isset($programID)){
                $selectedParticipants = $this->retrieveParticipants($state, $userID, $prorgamID, $startDate, $endDate);
            }

            if(isset($selectedParticipants)){

                $table = Datatables::of($selectedParticipants);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="justify-content-center">';
                    $btn .= '<a href="/joinprogram/' . $row->program_id . '"><span class="btn btn-primary"> Lihat </span></a>';

                    if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2 || $row->userID == $userID){
                        $btn .= '<a class="dismissAnchor" href="#" id="' . $row->participant_id . '"><span class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#dismissModal"> Padam </span></a>';
                    }

                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }
    }

    // Email user after add
    public function notifyUser($programID, $userID, $status){

        $program = Program::where('program_id', $programID)->select('programs.approved_at', 'programs.name')->first();

        $user = User::where('id', $userID)->select('email', 'username')->first();

        if($program->approved_at != null){
            $date = explode(" ", $program->approved_at);
            $program->approved_at = DateController::parseDate($date[0]) . ' ' . DateController::formatTime($date[1]);
        }

        Mail::to($user->email)->send(new NotifyParticipant([
            'name' => $user->username,
            'status' => $status,
            'program' => $program->name,
            'datetime' => $program->approved_at,
        ]));
    }
}
