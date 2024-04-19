<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Program;
use App\Models\Program_Spec;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;

class ProgramController extends Controller
{
    //
    public function index()
    {
        //
        if(Auth::check()){
            $logRole = Auth::user()->roleID;

            if($logRole){
                $users = User::where('roleID', $logRole)->get();
                $rolename = DB::table('roles')
                    ->where('roleID', $users[0]->roleID)
                    ->value("name");

                return view('programs.index', compact('users', 'rolename'));
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
            $program = new Program([
                'name' => $request->get('name'),
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'close_date' => $request->get('close_date'),
                'description' => $request->get('description'),
                'venue' => $request->get('address'),
                'type_id' => $request->get('programType'),
                'user_id' => Auth::user()->id,
                'status' => 1,
                'approved_status' => 1,
            ]);

            $program->save();

            $addVolunteer = new Program_Spec([
                'program_id' => $program->id,
                'user_type_id' => 2,
                'qty_limit' => $request->get('volunteer'),
                'qty_enrolled' => 0,
            ]);

            $addVolunteer->save();

            $addPoor = new Program_Spec([
                'program_id' => $program->id,
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
            ['approved_status', 1],
        ])
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
            $result = DB::table('programs')
                ->where('program_id', $id)
                ->update([
                    'name' => $request->get('name'),
                    'start_date' => $request->get('start_date'),
                    'start_time' => $request->get('start_time'),
                    'end_date' => $request->get('end_date'),
                    'end_time' => $request->get('end_time'),
                    'close_date' => $request->get('close_date'),
                    'description' => $request->get('description'),
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

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy($id)
    {
        //
        $result = DB::table('programs')
            ->where('program_id', $id)
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
            $rid = $request->rid;

            $selectedPrograms = DB::table('programs')
            ->join('users', 'users.id', '=', 'programs.user_id')
            ->where([
                ['programs.status', 1],
            ])
            ->select(
                'programs.program_id as program_id',
                'programs.user_id as user_id',
                'programs.name as name',
                'programs.start_date as start_date',
                'programs.end_date as end_date',
                'programs.start_time as start_time',
                'programs.end_time as end_time',
                'programs.close_date as close_date',
                'programs.venue as address',
                'programs.description as description',
                'programs.approved_status as approved_status',
                'users.name as username',
                'users.email as useremail',
                'users.contactNo as usercontact'
            )
            ->orderBy('programs.close_date')
            ->get();

            if(isset($selectedPrograms)){

                $table = Datatables::of($selectedPrograms);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    if($row->approved_status > 1 && (Auth::user()->roleID == 1 || Auth::user()->roleID == 2)){
                        $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-success" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a></div>';
                        $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a></div>';
                    }

                    if($row->approved_status == 1 && Auth::user()->id == $row->user_id){
                       
                        $btn = $btn . '<a href="/editprogram/' . $row->program_id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                        $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                    }

                    if($row->approved_status > 1)
                        $btn = $btn . '<a href="/joinprogram/' . $row->program_id . '"><span class="badge badge-success"> Mohon </span></a></div>';

                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }

        return view('programs.index');
    }
}
