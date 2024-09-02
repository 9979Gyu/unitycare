<?php

namespace App\Http\Controllers;

use App\Mail\NotifyParticipant;
use App\Models\Program;
use App\Models\Program_Spec;
use App\Models\Participant;
use App\Models\User;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportParticipant;
use App\Exports\ExportParticipatedProgram;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use DB;
use DataTables;
use App\Http\Controllers\PayPalController;

class ParticipantController extends Controller
{

    // Function to display list of participants 
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
                ->join('programs as p', 'p.user_id', '=', 'users.id')
                ->select('users.id', 'users.name')
                ->groupBy('users.id', 'users.name')
                ->orderBy('users.name')
                ->get();
            }
  
            return view('participants.index', compact('users', 'roleID'));
        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    // Function to display list of participated program 
    public function indexParticipated(){
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
                ->join('participants as p', 'p.user_id', '=', 'users.id')
                ->select('users.id', 'users.name')
                ->groupBy('users.id', 'users.name')
                ->orderBy('users.name')
                ->get();
            }
  
            return view('participants.indexApply', compact('users', 'roleID'));
        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    // Function to display create participants view
    public function create(Request $request, $id){

        if(Auth::check()){

            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            $type = $request->query('type', 'true');
            $action = $request->query('action', 'true');

            $program = Program::with('organization')
            ->where([
                ['program_id', $id],
                ['status', 1],
            ])
            ->select(
                '*',
            )
            ->first();

            $program->start_date = DateController::parseDate($program->start_date);
            $program->start_time = DateController::formatTime($program->start_time);
            $program->end_date = DateController::parseDate($program->end_date);
            $program->end_time = DateController::formatTime($program->end_time);
            $program->close_date = DateController::parseDate($program->close_date);
            
            $program->fee = number_format(json_decode($program->description, true)['fee'], 2);

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

            return view('participants.add', compact('program', 'volLimit', 'poorLimit', 'volRemain', 'poorRemain', 
            'participantExist', 'roleID', 'userID', 'type', 'action'));
            
        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);
    }

    // Function to store participants details
    public function store(Request $request){

        $programID = $request->get('programID');
        $userType = $request->get("button_id");
        $userID = Auth::user()->id;
        $organizerID = $request->get('organizerID');
        $amount = $request->get('amount');
        $result = null;

        if(isset($userType) && isset($programID)){

            // Peserta not sukarelawan
            if($userType == "3" && $amount != "0"){

                $organizerEmail = User::where('id', $organizerID)->value('email');

                // Check if paid before but quit
                $paymentExist = Transaction::where([
                    ['receiver_id', $organizerID],
                    ['payer_id', $userID],
                    ['payment_status', 1],
                    ['transaction_type_id', 2],
                    ['references->programID', $programID]
                ])
                ->orderBy('created_at', 'desc')
                ->first();

                if(!$paymentExist){
                    $paypalController = new PayPalController();

                    $data = [
                        'amount' => $amount,
                        'organizerID' => $organizerID,
                        'organizerEmail' => $organizerEmail,
                        'programID' => $programID,
                        'programName' => $request->get('programName'),
                        'userTypeID' => $userType,
                    ];
    
                    // Direct to payment
                    return $paypalController->userToOrganizerTransaction($data);
                }
                
            }

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

    // Function to update program status to 0
    public function destroy(Request $request){

        $partID = $request->get('selectedID');

        $result = Participant::where('participant_id', $partID)
            ->update([
                'status' => 0,
            ]);

        if($result){
            return response()->json(['message' => 'Berjaya dipadam']);
        }
        else{
            return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
        }
    }

    // Function to retrieve participants
    public function retrieveParticipants($state, $userID, $programID, $startDate, $endDate){

        $query = Participant::join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
        ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
        ->join('programs as p', 'p.program_id', '=', 'participants.program_id')
        ->join('types as t', 't.type_id', '=', 'p.type_id')
        ->join('users as program_creator', 'program_creator.id', '=', 'p.user_id')
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
            'participants.status',
            'joined_users.name as joined_username',
            'joined_users.email as joined_useremail',
            'dt.name as category',
            'p.name as program_name',
            'p.program_id',
            't.name as programtype',
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

        // Transform the data but keep it as a collection of objects
        $selectedParticipants->transform(function ($participant) {

            if($participant->created_at != null){
                $created_at = explode(' ', $participant->created_at);
                $participant->applied_date = DateController::parseDate($created_at[0]) . " " . DateController::formatTime($created_at[1]);
            }

            return $participant;
        });

        return $selectedParticipants;

    }

    // Function to retrieve participated program based on userid
    public function retrieveParticipatedProgram($state, $userID, $programID, $startDate, $endDate){

        $query = Program::where('programs.approved_status', 2)
        ->join('types as t', 't.type_id', '=', 'programs.type_id')
        ->join('participants as p', 'p.program_id', '=', 'programs.program_id')
        ->join('users as creator', 'creator.id', '=', 'programs.user_id');

        if($startDate != null && $endDate != null){
            $query->where([
                ['p.created_at', '>=', $startDate],
                ['p.created_at', '<=', $endDate],
            ]);
        }

        if($userID != "all"){
            $query = $query->where('p.user_id', $userID);
        }

        if($programID != "all"){
            $query = $query->where('p.program_id', $programID);
        }

        if($state == 0 || $state == 1){
            $query = $query->where('p.status', $state);
        }
        else{
            $query = $query->where([
                ['p.status', 1],
                ['p.user_type_id', $state], 
            ]);
        }

        $selectedParticipants = $query->select(
            'p.created_at',
            'p.participant_id',
            'p.user_id as joined_user_id',
            'p.status',
            'programs.name as program_name',
            'programs.program_id',
            'programs.description->desc as description',
            'programs.start_date',
            'programs.start_time',
            'programs.end_date',
            'programs.end_time',
            'programs.venue',
            'programs.postal_code',
            'programs.state',
            'programs.city',
            'programs.close_date',
            't.name as typename',
            'creator.name as creator_name',
            'creator.email as creator_email',
        )
        ->orderBy("p.created_at", "desc")
        ->get();

        // Transform the data but keep it as a collection of objects
        $selectedParticipants->transform(function ($participant) {

            if($participant->created_at != null){
                $created_at = explode(' ', $participant->created_at);
                $participant->applied_date = DateController::parseDate($created_at[0]) . " " . DateController::formatTime($created_at[1]);
            }

            $participant->start = DateController::parseDate($participant->start_date) . " " . DateController::formatTime($participant->start_time);
            $participant->end = DateController::parseDate($participant->end_date) . " " . DateController::formatTime($participant->end_time);

            $participant->address = $participant->venue . ', ' . $participant->postal_code . ', ' . $participant->city . ', ' . $participant->state;

            return $participant;
        });

        return $selectedParticipants;

    }

    // Function to display list of participant
    public function getParticipantsDatatable(Request $request){
        if(request()->ajax()){
            $state = $request->get('state');
            $programID = $request->get("programID");
            $userID = $request->get("userID");
            $startDate = $request->get("startDate");
            $endDate = $request->get("endDate");

            if(isset($programID)){
                $selectedParticipants = $this->retrieveParticipants($state, $userID, $programID, $startDate, $endDate);
            }

            if ($selectedParticipants === null || $selectedParticipants->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'draw' => $request->input('draw', 1), // Ensure to return the draw number
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                ]);
            }

            $table = Datatables::of($selectedParticipants);

            $table->addColumn('action', function ($row) use ($userID) {
                $token = csrf_token();
                $btn = '<div class="justify-content-center">';
                $btn .= '<a href="/joinprogram/' . $row->program_id . '?type=peserta"><span class="btn btn-primary"> Lihat </span></a>';

                // Can remove participant if is admin or staff
                if((Auth::user()->roleID == 1 || Auth::user()->roleID == 2) && $row->status == 1){
                    $btn .= '<a class="printAnchor" href="#" id="' . $row->participant_id . '"><span class="btn btn-warning m-1" data-bs-toggle="modal" data-bs-target="#printModal"> Sijil </span></a>';
                    $btn .= '<a class="dismissAnchor" href="#" id="' . $row->participant_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#dismissModal"> Padam </span></a>';
                }

                return $btn;
            });

            $table->rawColumns(['action']);
            return $table->make(true);
        }
    }

    // Function to display list of participanted program
    public function getParticipatedDatatable(Request $request){
        if(request()->ajax()){
            $state = $request->get('state');
            $programID = $request->get("programID");
            $userID = $request->get("userID");
            $startDate = $request->get("startDate");
            $endDate = $request->get("endDate");

            if(isset($programID)){
                $selectedParticipants = $this->retrieveParticipatedProgram($state, $userID, $programID, $startDate, $endDate);
            }

            if ($selectedParticipants === null || $selectedParticipants->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'draw' => $request->input('draw', 1), // Ensure to return the draw number
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                ]);
            }

            $table = Datatables::of($selectedParticipants);
            $now = date('Y-m-d');

            $table->addColumn('action', function ($row) use ($userID, $now) {
                $token = csrf_token();
                $btn = '<div class="justify-content-center">';
                $btn .= '<a href="/joinprogram/' . $row->program_id . '?type=sertai"><span class="btn btn-primary"> Lihat </span></a>';

                // Can remove participant if is admin or staff
                if((Auth::user()->roleID == 1 || $row->joined_user_id == $userID) && $row->status == 1){
                    if($row->close_date >= $now)
                        $btn .= '<a class="dismissAnchor" href="#" id="' . $row->participant_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#dismissModal"> Tarik Diri </span></a>';
                    elseif($row->end_date < $now){
                        $btn .= '<a class="printAnchor" href="#" id="' . $row->participant_id . '"><span class="btn btn-warning m-1" data-bs-toggle="modal" data-bs-target="#printModal"> Sijil </span></a>';
                        $btn .= '<a class="deleteAnchor" href="#" id="' . $row->participant_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                    }
                }

                return $btn;
            });

            $table->rawColumns(['action']);
            return $table->make(true);
        }
    }

    // Reuse function for set query for get program participant count
    public function retrievePartQuery($state, $programID, $userID, $startDate, $endDate, $types){
        $query = Participant::join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
        ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
        ->join('programs as p', 'p.program_id', '=', 'participants.program_id')
        ->join('types as t', 't.type_id', '=', 'p.type_id')
        ->join('users as program_creator', 'program_creator.id', '=', 'p.user_id')
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
            if($types == "creator"){
                $query = $query->where('p.user_id', $userID);
            }
            else{
                $query = $query->where('participants.user_id', $userID);
            }
        }

        if($programID != "all"){
            $query = $query->where('participants.program_id', $programID);
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

        return $query;
    }

    // Function to display number of participant by user type for active program
    public function programTypePieChart(Request $request){

        $state = $request->get('state');
        $programID = $request->get("programID");
        $userID = $request->get("userID");
        $startDate = $request->get("startDate");
        $endDate = $request->get("endDate");

        if(isset($programID)){

            $query = $this->retrievePartQuery($state, $programID, $userID, $startDate, $endDate, "join");

            $num = $query->groupBy('ut.name')
            ->selectRaw('ut.name as labels, COUNT(*) as data')
            ->get();

            return response()->json([
                'labels' => $num->pluck('labels'),
                'data' => $num->pluck('data'),
            ]);

        }

    }

    // Function to display number of participant for active program
    public function programBarChart(Request $request){

        $state = $request->get('state');
        $programID = $request->get("programID");
        $userID = $request->get("userID");
        $startDate = $request->get("startDate");
        $endDate = $request->get("endDate");

        if(isset($programID)){

            $query = $this->retrievePartQuery($state, $programID, $userID, $startDate, $endDate, "join");

            $num = $query->groupBy('p.name')
            ->selectRaw('p.name as labels, COUNT(*) as data')
            ->get();

            return response()->json([
                'labels' => $num->pluck('labels'),
                'data' => $num->pluck('data'),
            ]);

        }

    }

    // Reuse function for set query for get program participant count
    public function retrieveParticipantQuery($state, $programID, $userID, $startDate, $endDate){
        $query = Participant::join('users as joined_users', 'joined_users.id', '=', 'participants.user_id')
        ->join('user_types as ut', 'ut.user_type_id', '=', 'participants.user_type_id')
        ->join('programs as p', 'p.program_id', '=', 'participants.program_id')
        ->join('types as t', 't.type_id', '=', 'p.type_id')
        ->join('users as program_creator', 'program_creator.id', '=', 'p.user_id')
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
            $query = $query->where('participants.user_id', $userID);
        }

        if($programID != "all"){
            $query = $query->where('participants.program_id', $programID);
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

        return $query;
    }

    // Function to display number of participant by user type for active program
    public function participantTypePieChart(Request $request){

        $state = $request->get('state');
        $programID = $request->get("programID");
        $userID = $request->get("userID");
        $startDate = $request->get("startDate");
        $endDate = $request->get("endDate");

        if(isset($programID)){

            $query = $this->retrievePartQuery($state, $programID, $userID, $startDate, $endDate, "creator");

            $num = $query->groupBy('ut.name')
            ->selectRaw('ut.name as labels, COUNT(*) as data')
            ->get();


            return response()->json([
                'labels' => $num->pluck('labels'),
                'data' => $num->pluck('data'),
            ]);

        }

    }

    // Function to display number of participant for active program
    public function participantBarChart(Request $request){

        $state = $request->get('state');
        $programID = $request->get("programID");
        $userID = $request->get("userID");
        $startDate = $request->get("startDate");
        $endDate = $request->get("endDate");

        if(isset($programID)){

            $query = $this->retrievePartQuery($state, $programID, $userID, $startDate, $endDate, "creator");

            $num = $query->groupBy('p.name')
            ->selectRaw('p.name as labels, COUNT(*) as data')
            ->get();

            return response()->json([
                'labels' => $num->pluck('labels'),
                'data' => $num->pluck('data'),
            ]);

        }

    }

    // Function to export participants info
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
            $state = $request->get('statusFilter');
            $programID = $request->get("program");
            $userID = $request->get("organization");
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if(isset($programID)){
                $selectedParticipants = $this->retrieveParticipants($state, $userID, $programID, $startDate, $endDate);

                return Excel::download(new ExportParticipant($selectedParticipants), 
                    'Senarai peserta program - ' . time() . '.xlsx'
                );
            }
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

    public function exportParticipated(Request $request){
        
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
            $state = $request->get('statusFilter');
            $programID = $request->get("program");
            $userID = $request->get("organization");
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if(isset($programID)){
                $selectedPrograms = $this->retrieveParticipatedProgram($state, $userID, $programID, $startDate, $endDate);

                return Excel::download(new ExportParticipatedProgram($selectedPrograms), 
                    'Senarai program sertai - ' . time() . '.xlsx'
                );
            }
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
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
