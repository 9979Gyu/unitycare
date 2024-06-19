<?php

namespace App\Http\Controllers;

use App\Mail\NotifyJoinEmail;
use App\Models\Program;
use App\Models\Program_Spec;
use App\Models\Participant;
use App\Models\User;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportProgramReport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;



class ProgramController extends Controller
{
    // Function to update the program status if expired
    public static function updateProgramStatus(){
        $currentDate = date('Y-m-d');
        $currentTime = date('H:i:s');
        
        // Update outdated program status to 0;
        Program::where([
            ['status', 1],
            ['end_date', '<', $currentDate],  
            ['end_time', '<', $currentTime],  
        ])
        ->update(['status' => 0]);
    }

    // Function to display view for program based on role
    public function index(){

        $this->updateProgramStatus();

        if(Auth::check()){

            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            if($roleID <= 4){
                // Get the list of user created program
                $query = User::where('users.status', 1)
                ->join('programs as p', 'p.user_id', 'users.id');

                if($roleID == 3 || $roleID == 4){
                    $query = $query->where('users.id', $userID);
                }

                $users = $query->select('users.id', 'users.name')
                ->groupBy('users.id', 'users.name')
                ->orderBy('users.name')
                ->get();

                return view('reports.programs.index', compact('roleID', 'users'));
            }

            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);
        
    }

    // Function to display view for program for poor and volunteer
    public function view(){
        if(Auth::check()){

            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            return view('programs.view', compact('roleID', 'userID'));

        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);
    }

    // Function to get programs based on user ID
    public function geProgramsByUser(Request $request){
        $userID = $request->get('selectedUser');
        $roleID = Auth::user()->roleID;

        if(isset($userID)){

            $query = Program::select('program_id', 'name')
                ->groupBy('program_id', 'name');

            if($roleID != 1 && $roleID != 2){
                $query = $query->where('status', 1);
            }

            if($userID != 'all'){
                $query = $query->where('user_id', $userID);
            }
            
            $programs = $query->get();

            return response()->json($programs);

        }
    }

    // Function to get the programs based on condition (Reusable)
    public function retrievePrograms($userID, $state, $program, $status, $startDate, $endDate){
        if($state == 4){
            $status = 0;
        }

        $query = Program::where('programs.status', $status)
        ->join('users as u', 'u.id', '=', 'programs.user_id')
        ->leftJoin('users as processed', function($join) {
            $join->on('processed.id', '=', 'programs.approved_by')
                 ->whereNotNull('programs.approved_by');
        })
        ->join('types as t', 't.type_id', '=', 'programs.type_id')
        ->join('program_specs as ps1', 'ps1.program_id', '=', 'programs.program_id')
        ->where('ps1.user_type_id', 2)
        ->join('program_specs as ps2', 'ps2.program_id', '=', 'programs.program_id')
        ->where('ps2.user_type_id', 3);

        if($startDate != null && $endDate != null){
            $query->where([
                ['programs.start_date', '>=', $startDate],
                ['programs.start_date', '<=', $endDate],
            ]);
        }

        if($userID != "all") {
            $query->where('programs.user_id', $userID);
        }

        if($state != 3 && $state != 4) {
            $query->where('programs.approved_status', $state);
        }

        if($program == 'vol'){
            $query->where('t.type_id', 1);
        }
        elseif($program == 'skill'){
            $query->where('t.type_id', 2);
        }
        elseif($program != 'all'){
            $query->where('programs.program_id', $program);
        }

        $selectedPrograms = $query->select(
            'programs.*',
            'programs.description->desc as description',
            'programs.description->reason as reason',
            't.name as typename',
            'u.name as username',
            'u.email as useremail',
            'u.contactNo as usercontact',
            'processed.name as processedname',
            'processed.email as processedemail',
            'ps1.qty_limit as vol_qty_limit',
            'ps1.qty_enrolled as vol_qty_enrolled',
            'ps2.qty_limit as poor_qty_limit',
            'ps2.qty_enrolled as poor_qty_enrolled',
        )
        ->orderBy('programs.updated_at', 'desc')
        ->get();

        // Transform the data but keep it as a collection of objects
        $selectedPrograms->transform(function ($program) {

            if($program->approved_at != null){
                $approved_at = explode(' ', $program->approved_at);
                $program->approved_at = DateController::parseDate($approved_at[0]) . ' ' . DateController::formatTime($approved_at[1]);
            }

            if($program->approved_status == 0){
                $approval = "Ditolak: " . $program->reason;
            }
            elseif($program->approved_status == 1){
                $approval = "Belum Diproses";
            }
            else{
                $approval = "Telah Diluluskan";
            }

            $program->approval = $approval;

            $program->address = $program->venue . ', ' . $program->postal_code . 
            ', ' . $program->city . ', ' . $program->state;

            $program->vol = $program->vol_qty_limit . ' orang';
            $program->poor = $program->poor_qty_limit . ' orang';

            $startDate = $program->start_date;
            $program->start_date = DateController::parseDate($startDate);
            $program->start_time = DateController::formatTime($program->start_time);
            $program->start = $program->start_date . ' ' . $program->start_time;

            $endDate = $program->end_date;
            $program->end_date = DateController::parseDate($endDate);
            $program->end_time = DateController::formatTime($program->end_time);
            $program->end = $program->end_date . ' ' . $program->end_time;

            $closeDate = $program->close_date;
            $program->close_date = DateController::parseDate($closeDate);

            return $program;
        });

        return $selectedPrograms;

    }

    // Function to return the list of programs based on condition
    public function getProgramsDatatable(Request $request){
        if(request()->ajax()){
            $userID = $request->get('selectedUser');
            $state = $request->get('selectedState');
            $program = $request->get('selectedType');
            $status = 1;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            // Handling for retrieve programs based on approval state and program type
            if(isset($userID) && isset($state) && isset($program) && isset($status)){
                $selectedPrograms = $this->retrievePrograms($userID, $state, $program, $status, $startDate, $endDate); 
            }

            if(isset($selectedPrograms)){

                $table = Datatables::of($selectedPrograms);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="justify-content-center">';
                    $btn .= '<a href="/joinprogram/' . $row->program_id . '"><span class="btn btn-primary"> Lihat </span></a>';

                    if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                        if($row->approved_status == 1){
                            // User created the program and the program is pending approval
                            if($row->user_id == Auth::user()->id){
                                $btn .= '<a href="/editprogram/' . $row->program_id . '"><span class="btn btn-warning m-1"> Kemaskini </span></a>';
                                $btn .= '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                            }
                            $btn .= '<a class="approveAnchor" href="#" id="' . $row->program_id . '"><span class="btn btn-success m-1" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a>';
                            $btn .= '<a class="declineAnchor" href="#" id="' . $row->program_id . '"><span class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';
                        }
                        // The program is declined
                        elseif($row->approved_status == 0){
                            if($row->user_id == Auth::user()->id){
                                $btn .= '<a href="/editprogram/' . $row->program_id . '"><span class="btn btn-warning m-1"> Kemaskini </span></a>';
                                $btn .= '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                            }
                        }
                    }
                    // User is enterprise or volunteer
                    elseif(Auth::user()->roleID == 3 || Auth::user()->roleID == 4){
                        // User created the program and the program is pending approval
                        if($row->approved_status <= 1 && $row->user_id == Auth::user()->id){

                            $btn .= '<a href="/editprogram/' . $row->program_id . '"><span class="btn btn-warning m-1"> Kemaskini </span></a>';
                            $btn .= '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                            
                        }
                        elseif($row->approved_status == 2 && $row->user_id == Auth::user()->id){
                            $btn .= '<a class="boostAnchor" href="#" id="' . $row->program_id . '"><span class="btn btn-warning m-1" data-bs-toggle="modal" data-bs-target="#boostModal"> Galak </span></a>';
                            $btn .= '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                        }
                    }

                    if($row->status == 0){
                        $btn = " ";
                    }

                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }
    }

    // Function to update program updateAt value
    public function boostProgram(Request $request){
        $programID = $request->get('selectedID');

        if(isset($programID)){
            Program::where([
                ['program_id', $programID],
                ['status', 1],
                ['approved_status', 2],
            ])
            ->update([
                'updated_at' => date('Y-m-d H:i:s')
            ]);

            return redirect('/viewprogram')->with('success', 'Data berjaya dikemaskini');
        }

        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);

    }

    // Function to export the list of programs based on condition
    public function exportPrograms(Request $request){

        // Validate the request data
        $rules = [
            'statusFilter' => 'required',
            'type' => 'required',
            'organization' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            $userID = $request->get('organization');
            $state = $request->get('statusFilter');
            $program = $request->get('type');
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');
            $status = 1;

            $selectedPrograms = $this->retrievePrograms($userID, $state, $program, $status, $startDate, $endDate);

            return Excel::download(new ExportProgramReport($selectedPrograms), 
                'Senarai Program - ' . time() . '.xlsx'
            );

        }

        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

    public function getUpdatedPrograms(Request $request){

        $userID = $request->get('userID');
        $today = date('Y-m-d');

        $activePrograms = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 2],
            ['close_date', '>=', $today],
        ])
        ->join('users as u', 'u.id', '=', 'programs.user_id')
        ->join('types as t', 't.type_id', '=', 'programs.type_id')
        ->select(
            'programs.*', 
            'programs.description->desc as description',
            'programs.description->reason as reason',
            'u.id as userid',
            'u.name as username', 
            'u.contactNo as usercontact', 
            'u.email as useremail',
            't.name as typename'
        )
        ->orderBy('programs.updated_at', 'desc')
        ->get();

        $enrolled = Participant::where([
            ['user_id', $userID],
            ['status', 1]
        ])
        ->select('program_id as pid')
        ->get();

        return response()->json([
            'activePrograms' => $activePrograms,
            'enrolled' => $enrolled
        ]);        
    }

    // Function to update program status to 0
    public function destroy(Request $request){

        $programID = $request->get('selectedID');

        $result = Program::where('programs.program_id', $programID)
            ->update([
                'status' => 0,
            ]);

        if($result){

            // destroy relative participants
            $update = Participant::where('program_id', $programID)
            ->update([
                'status' => 0,
            ]);

            if($update){

                // Notify participants
                $this->notifyJoin($programID);

                return response()->json(['message' => 'Berjaya dipadam']);
            }

        }
        else{
            return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
        }
    }

    // Function to update approval details
    public function updateApproval(Request $request){
        $programID = $request->get('selectedID');
        $approval = $request->get('approval');
        $userID = Auth::user()->id;
        $now = date('Y-m-d H:i:s');

        if($approval == 2){
            $update = Program::where([
                ['program_id', $programID],
                ['status', 1],
            ])
            ->update([
                'approved_status' => 2,
                'approved_by' => $userID,
                'approved_at' => date('Y-m-d H:i:s'),
                'updated_at' => $now,
            ]);
        }
        else{
            $reason = $request->get('reason');

            // Get the current description
            $currentDesc = Program::where('program_id', $programID)
            ->value('description');

            // Decode the JSON to an associative array
            $descArray = json_decode($currentDesc, true);

            // Update the 'reason' field
            $descArray['reason'] = $reason;

            // Encode the array back to JSON
            $newDesc = json_encode($descArray);

            $update = Program::where('program_id', $programID)
            ->where('status', 1)
            ->update([
                'approved_status' => 0,
                'approved_by' => $userID,
                'approved_at' => $now,
                'description' => $newDesc,
                'updated_at' => $now,
            ]);

        }

        // If successfully update the program
        if($update){

            $this->notifyUser($programID);

            // direct user to view program page with success messasge
            return redirect('/viewprogram')->with('success', 'Data berjaya dikemaskini');
        }
        else{
            // direct user to view program page with error messasge
            return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);
        }

    }

    // Function to display edit program view
    public function edit($id){

        if(Auth::check()){

            $program = Program::where([
                ['program_id', $id],
                ['status', 1],
                ['approved_status', '<', 2],
            ])
            ->select(
                "*",
                "description->desc as description"
            )
            ->first();
    
            if(isset($program)){
                $volNum = Program_Spec::where([
                    ['program_id', $id],
                    ['user_type_id', 2],
                ])
                ->first();
        
                $poorNum = Program_Spec::where([
                    ['program_id', $id],
                    ['user_type_id', 3],
                ])
                ->first();
                
                return view('programs.edit', compact('program', 'volNum', 'poorNum'));
                
            }

            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

        }
        else{
            
            return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

        }

    }

    // Function to update the program edited details
    public function update(Request $request){
        $rules = [
            'name' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
            'close_date' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'description' => 'required',
            'address' => 'required',
            'volunteer' => 'required',
            'poor' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){

            $programID = $request->get("programID");
            $userID = Auth::user()->id;

            $desc = [
                "desc" => ucwords(trim($request->get('description'))),
                "reason" => "",
            ];

            $name = ucwords(trim($request->get('name')));

            $result = Program::where([
                ['program_id', $programID],
                ['user_id', $userID]
            ])
            ->update([
                'name' => $name,
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'close_date' => $request->get('close_date'),
                'description' => json_encode($desc),
                'venue' => ucwords(trim($request->get('address'))),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'postal_code' => $request->get('postalCode'),
                'user_id' => $userID,
                'status' => 1,
                'approved_status' => 1,
                'approved_by' => null,
                'approved_at' => null,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if($result){

                $updateVol = Program_Spec::where([
                    ['program_id', $programID],
                    ['user_type_id', 2],
                ])
                ->update([
                    'qty_limit' => $request->get('volunteer'),
                ]);

                $updatePoor = Program_Spec::where([
                    ['program_id', $programID],
                    ['user_type_id', 3],
                ])
                ->update([
                    'qty_limit' => $request->get('poor'),
                ]);

                $this->notifyUser($programID);

                return redirect('/viewprogram')->with('success', 'Data berjaya dikemaskini');
            }

            return redirect()->back()->withErrors(['message' => 'Data tidak berjaya dikemaskini']);
  
        }
        else{
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = $validator->errors();
    
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors(['message' => $errors->all]);

            }
        }
    }

    // Function to display create program view
    public function create(){

        $roleID = Auth::user()->roleID != 5;

        if(Auth::check() && $roleID){

            return view('programs.add', compact('roleID'));
        }
        else{
            return redirect('/viewprogram')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }

    }

    // Function to store program details
    public function store(Request $request){
        $rules = [
            'name' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
            'close_date' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'description' => 'required',
            'address' => 'required',
            'volunteer' => 'required',
            'poor' => 'required',
            'programType' => 'required',
            'roleID' => 'required|in:1,2,3,4'
        ];

        $validated = $request->validate($rules);

        if($validated){

            $desc = [
                "desc" => ucwords(trim($request->get('description'))),
                "reason" => "",
            ];

            $program = new Program([
                'name' => ucwords(trim($request->get('name'))),
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'close_date' => $request->get('close_date'),
                'description' => json_encode($desc),
                'venue' => ucwords(trim($request->get('address'))),
                'type_id' => $request->get('programType'),
                'user_id' => Auth::user()->id,
                'status' => 1,
                'approved_status' => 1,
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'postal_code' => $request->get('postalCode'),
            ]);

            $program->save();

            $addVolunteer = new Program_Spec([
                'program_id' => $program->program_id,
                'user_type_id' => 2,
                'qty_limit' => $request->get('volunteer'),
                'qty_enrolled' => 0,
            ]);

            $addVolunteer->save();

            $addPoor = new Program_Spec([
                'program_id' => $program->program_id,
                'user_type_id' => 3,
                'qty_limit' => $request->get('poor'),
                'qty_enrolled' => 0,
            ]);

            $addPoor->save();

            $this->notifyUser($program->program_id);

            return redirect('/viewprogram')->with('success', 'Program berjaya didaftarkan');
        }
        else{
            $validator = Validator::make($request->all(), $rules);
            if ($validator->fails()) {
                $errors = $validator->errors();
    
                return redirect()->back()
                    ->withInput($request->all())
                    ->withErrors(['message' => $errors->all]);

            }
        }
    }

    // Email user after approval
    public function notifyUser($programID){

        $program = Program::where('programs.program_id', $programID)
        ->join('users as u', 'u.id', '=', 'programs.user_id')
        ->select(
            'programs.approved_at',
            'programs.approved_status',
            'programs.name',
            'programs.description->reason as reason',
            'u.email', 
            'u.username'
        )
        ->first();

        if($program->approved_at != null){
            $date = explode(" ", $program->approved_at);
            $program->approved_at = DateController::parseDate($date[0]) . ' ' . DateController::formatTime($date[1]);
        }

        Mail::to($program->email)->send(new NotifyJoinEmail([
            'name' => $program->username,
            'subject' => 'program',
            'approval' => $program->approved_status,
            'offer' => $program->name,
            'datetime' => $program->approved_at,
            'reason' => $program->reason ? $program->reason : "",
        ]));
    }

    public function notifyJoin($programID){

        $programs = Participant::where('participants.program_id', $programID)
        ->join('programs as p', 'p.program_id', '=', 'participants.program_id')
        ->join('users as u', 'u.id', '=', 'participants.user_id')
        ->select(
            'programs.approved_at',
            'programs.approved_status',
            'programs.name',
            'programs.description->reason as reason',
            'u.email', 
            'u.username'
        )
        ->get();

        foreach($programs as $program){

            if($program->approved_at != null){
                $date = explode(" ", $program->approved_at);
                $program->approved_at = DateController::parseDate($date[0]) . ' ' . DateController::formatTime($date[1]);
            }

            Mail::to($program->email)->send(new NotifyJoinEmail([
                'name' => $program->username,
                'subject' => 'program',
                'approval' => $program->approved_status,
                'offer' => $program->name,
                'datetime' => $program->approved_at,
                'reason' => $program->reason ? $program->reason : "",
            ]));
        }
    }
}
