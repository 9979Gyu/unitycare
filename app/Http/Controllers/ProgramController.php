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
use App\Exports\ExportProgram;
use Maatwebsite\Excel\Facades\Excel;

class ProgramController extends Controller
{
    //

    public static function getProgramsByApprovedStatus(){

        $roleNo = 0;

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
        }

        $allPrograms = Program::where('programs.status', 1)
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->with('organization')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        $approvedPrograms = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 2]
        ])
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        $pendingPrograms = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 1]
        ])
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        $declinedPrograms = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 0]
        ])
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        if($roleNo == 5){
            $programs = $approvedPrograms;
        }
        else{
            $programs = $allPrograms;
        }

        return $programs;

    }

    public function index()
    {
        $currentDate = date('Y-m-d');
        
        // Update outdated program status to 0;
        Program::where([
            ['status', 1],
            // Check for end_date before current date
            ['end_date', '<', $currentDate],  
        ])
        ->update(['status' => 0]);

        if(Auth::check()){
            if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2)
                return view('programs.index');
            else{
                return view('programs.view');
            }

        }
        return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        
    }

    public function create($roleNo)
    {
        //
        if(Auth::check() && Auth::user()->roleID != 5){
            return view('programs.add', compact('roleNo'));
        }
        else{
            return redirect('/viewprogram')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
        
    }

    public function store(Request $request)
    {
        //
        $rules = [
            'name' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
            'close_date' => 'required',
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
                "desc" => $request->get('description'),
                "reason" => "",
            ];

            $program = new Program([
                'name' => $request->get('name'),
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'close_date' => $request->get('close_date'),
                'description' => json_encode($desc),
                'venue' => $request->get('address'),
                'type_id' => $request->get('programType'),
                'user_id' => Auth::user()->id,
                'status' => 1,
                'approved_status' => 1,
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

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        //
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

        if(Auth::check()){
            return view('programs.edit', compact('program', 'volNum', 'poorNum'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, $id)
    {
        //
        $rules = [
            'name' => 'required',
            'start_date' => 'required',
            'start_time' => 'required',
            'end_date' => 'required',
            'end_time' => 'required',
            'close_date' => 'required',
            'description' => 'required',
            'address' => 'required',
            'volunteer' => 'required',
            'poor' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){

            $desc = [
                "desc" => $request->get('description'),
                "reason" => "",
            ];

            $result = DB::table('programs')
                ->where('program_id', $id)
                ->update([
                    'name' => $request->get('name'),
                    'start_date' => $request->get('start_date'),
                    'start_time' => $request->get('start_time'),
                    'end_date' => $request->get('end_date'),
                    'end_time' => $request->get('end_time'),
                    'close_date' => $request->get('close_date'),
                    'description' => json_encode($desc),
                    'venue' => $request->get('address'),
                    'user_id' => Auth::user()->id,
                    'status' => 1,
                    'approved_status' => 1,
                ]);

            if($result){
                $updateVol = DB::table('program_specs')
                ->where([
                    ['program_id', $id],
                    ['user_type_id', 2],
                ])
                ->update([
                    'qty_limit' => $request->get('volunteer'),
                ]);

                $updatePoor = DB::table('program_specs')
                ->where([
                    ['program_id', $id],
                    ['user_type_id', 3],
                ])
                ->update([
                    'qty_limit' => $request->get('poor'),
                ]);
            }
            

            return redirect('/viewprogram')->with('success', 'Data berjaya dikemaskini');
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

    // Return the program details based on given program id
    public function getProgramById(Request $request)
    {
        if(request()->ajax()){
            $pid = $request->pid;

            $participants = Program_Spec::where('program_id', $pid)
            ->get();

            // Join programs, program_spec, and users tables (using relationships)
            $program = DB::table('programs')
            ->join('users as u', 'u.id', '=', 'programs.user_id')
            ->where([
                ['programs.status', 1],
                ['programs.program_id', $pid],
            ])
            ->select([
                'programs.*',
                'u.name as username',
            ])
            ->first();

            $data = [
                'program' => $program,
                'participants' => $participants,
            ];

            return response()->json($data);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function updateApproval($id)
    {
        // Update the program details
        $update = DB::table('programs')
            ->where([
                ['program_id', $id],
                ['status', 1],
            ])
            ->update([
                'approved_status' => 2,
                'approved_by' => Auth::user()->id, 
            ]);

        // If successfully update the program
        if($update){
            // direct user to view program page with success messasge
            return redirect('/viewprogram')->with('success', 'Data berjaya dikemaskini');
        }
        else{
            // direct user to view program page with error messasge
            return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);
        }

    }

    public function declineApproval(Request $request)
    {
        // Get the current description
        $currentDesc = DB::table('programs')
        ->where('program_id', $request->selectedID)
        ->value('description');

        // Decode the JSON to an associative array
        $descArray = json_decode($currentDesc, true);

        // Update the 'reason' field
        $descArray['reason'] = $request->get('reason');

        // Encode the array back to JSON
        $newDesc = json_encode($descArray);

        // Update the program details
        $update = DB::table('programs')
        ->where([
            ['program_id', $request->selectedID],
            ['status', 1],
        ])
        ->update([
            'approved_status' => 0,
            'approved_by' => Auth::user()->id, 
            'description' => $newDesc
        ]);

        // If successfully update the program
        if($update){
            // direct user to view program page with success messasge
            return redirect('/viewprogram')->with('success', 'Data berjaya dikemaskini');
        }
        else{
            // direct user to view program page with error messasge
            return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);
        }

    }

    /**
     * Remove the specified resource from storage.
     *
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request)
    {
        //
        $result = DB::table('programs')
            ->where('program_id', $request->selectedID)
            ->update([
                'status' => 0,
            ]);

        if($result){
            return redirect()->back()->with('success', 'Berjaya dipadam');
        }
        else{
            return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
        }
    }
    
    // Function to get list of programs
    public function getProgramsDatatable(Request $request)
    {
        if(request()->ajax()){
            $rid = $request->get('rid');
            $state = $request->get('selectedState');
            $type = $request->get('selectedType');
            $status = $request->get('status');

            // Handling for retrieve programs based on approval state and program type
            if(isset($rid) && isset($state) && isset($type) && isset($status)){

                $query = Program::where('status', $status)->with('organization');

                if($state != 3) {
                    $query->where('approved_status', $state);
                }

                if($type != 3) {
                    $query->where('type_id', $type);
                }

                $selectedPrograms = $query->orderBy('updated_at', 'desc')->get();

                // Transform the data but keep it as a collection of objects
                $selectedPrograms->transform(function ($program) {
                    $program->description = json_decode($program->description, true)['desc'] ?? '';
                    $program->username = $program->organization->name ?? '';
                    $program->useremail = $program->organization->email ?? '';
                    $program->usercontact = $program->organization->contactNo ?? '';
                    return $program;
                });

            }

            if(isset($selectedPrograms)){

                $table = Datatables::of($selectedPrograms);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';

                    if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                        if($row->approved_status == 1){
                            // User created the program and the program is pending approval
                            if($row->user_id == Auth::user()->id){
                                $btn = $btn . '<a href="/editprogram/' . $row->program_id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                                $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                            }
                            $btn = $btn . '<a class="approveAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-success" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a></div>';
                            $btn = $btn . '<a class="declineAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a></div>';
                        }
                        // The program is approved
                        elseif($row->approved_status == 2){
                            // $btn = $btn . '<a href="/joinprogram/' . $row->program_id . '"><span class="badge badge-success"> Mohon </span></a></div>';
                        }
                        // The program is declined
                        elseif($row->approved_status == 0){
                            if($row->user_id == Auth::user()->id){
                                $btn = $btn . '<a href="/editprogram/' . $row->program_id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                                $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                            }
                        }
                    }
                    // User is enterprise or volunteer
                    elseif(Auth::user()->roleID == 3 || Auth::user()->roleID == 4){
                        // User created the program and the program is pending approval
                        if(($row->approved_status == 1 || $row->approved_status == 0) && $row->user_id == Auth::user()->id){

                            $btn = $btn . '<a href="/editprogram/' . $row->program_id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                            $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                            
                        }
                        // The program is approved
                        elseif($row->approved_status == 2){
                            $btn = $btn . '<a href="/joinprogram/' . $row->program_id . '"><span class="badge badge-success"> Mohon </span></a></div>';
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

        return view('programs.index');
    }

    public function getUpdatedPrograms(){
        $allPrograms = Program::where([
            ['programs.status', 1],
        ])
        ->join('users', 'id', '=', 'user_id')
        ->join('types', 'types.type_id', '=', 'programs.type_id')
        ->select(
            'programs.*', 
            'programs.description->desc as description',
            'programs.description->reason as reason',
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail',
            'types.name as typename'
        )
        ->orderBy('programs.updated_at', 'desc')
        ->get();

        $enrolled = Participant::where([
            ['user_id', Auth::user()->id],
            ['status', 1],
        ])
        ->select('program_id as pid')
        ->get();

        return response()->json([
            'allPrograms' => $allPrograms,
            'enrolled' => $enrolled
        ]);        
    }

    // Function to export program info
    public function exportPrograms(Request $request){
        
        // Validate the request data
        $rules = [
            'roleID' => 'required',
            'type' => 'required',
            'statusFilter' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            $state = $request->get('statusFilter');
            $type = $request->get('type');
            $status = 1;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if($state == 4){
                $status = 0;
            }
            
            return Excel::download(new ExportProgram(
                $roleID, $state, $type, $status, $startDate, $endDate), 
                'Programs-' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }
}
