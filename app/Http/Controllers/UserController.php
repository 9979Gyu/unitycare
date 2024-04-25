<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Poor;
use Facade\FlareClient\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($roleNo)
    {
        //
        if(Auth::check()){
            $logRole = Auth::user()->roleID;

            if($logRole == 1 || ($logRole == 2 && $roleNo > 2)){
                $users = User::where('roleID', $roleNo)->get();
                
                $rolename = DB::table('roles')
                    ->where('roleID', $users[0]->roleID)
                    ->value("name");

                return view('users.index', compact('users', 'rolename'));
            }

        }
        return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($roleNo)
    {
        // Show add enterprise page
        if($roleNo == 3)
            return view('users.enterprise.add', compact('roleNo')); 
        else
            return view('users.add', compact('roleNo'));  
    }

    public function createPoorPeople(){
        
        if(Auth::check()){
            $disTypes = DB::table('disability_types')
            ->where('status', 1)
            ->select('dis_type_id', 'name')
            ->get();
    
            return view('users.poor.add', compact('disTypes'));
        }
        else{
            return redirect('/login')->withErrors(['message' => 'Anda perlu log masuk untuk mendaftarkan orang kurang upaya.']);
        }
    }

    public function checkUser(Request $request){

        $result = 0;

        $type = $request->get('usertype');

        if($type == "poor"){
            $number = $request->get('ic');
        }
        else{
            $number = $request->get('ssm');
        }

        if(isset($number)){
            if($type == "poor"){
                $result = DB::connection('mysqlSecondConnection')
                ->table('users')
                ->where('users.ICNo', $number)
                ->first();
            }
            else{
                $result = DB::connection('mysqlSSMConnection')
                ->table('enterprises')
                ->where([
                    ['registrationNo', $number],
                    ['status', 1],
                ])
                ->first();
            }

            if($result){
                return response()->json(['success' => true, 'user' => $result]);
            }
        }

        if($type=="poor"){
            return redirect('/createspecial')->withErrors(["message" => "Anda tidak dibenarkan untuk melayari halaman ini"]);
        }
        else{
            return redirect('/')->withErrors(["message" => "Pendaftaran tidak berjaya"]);
        }
    }

    public function checkEnterprise(Request $request){
        $ssmRegNo = $request->get('ssm');

        if(isset($ssmRegNo)){
            $enterprise = DB::connection('mysqlSSMConnection')
                ->table('enterprises')
                ->where('enterprises.registrationNo', $ssmRegNo)
                ->first();

            if($user){
                return response()->json(['success' => true, 'user' => $user]);
            }
            else{
                return redirect('/createspecial')->withErrors(["message" => "Anda tidak dibenarkan untuk melayari halaman ini"]);
            }
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {

        $roleID = $request->get('roleID');

        if($roleID == 1 || $roleID == 2 || $roleID == 4 || $roleID == 5){
            // store as admin / staff / volunteer / poor people
            $rules = [
                'name' => 'required',
                'ICNo' => 'required|unique:users,ICNo',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'username' => 'required|unique:users,username',
                'contactNo' => 'required|unique:users,contactNo',
                'address' => 'required',
                'state' => 'required',
                'city' => 'required',
                'postalCode' => 'required',
                'roleID' => 'required|in:1,2,4,5',
                'disType' => 'required|integer|between:1,7'
            ];
        }
        else if($roleID == 3){
            // store as enterprise
            $rules = [
                'name' => 'required',
                'regNo' => 'required|unique:users,ICNo',
                'email' => 'required|unique:users,email',
                'password' => 'required',
                'username' => 'required|unique:users,username',
                'contactNo' => 'required|unique:users,contactNo',
                'address' => 'required',
                'state' => 'required',
                'city' => 'required',
                'postalCode' => 'required',
                'roleID' => 'required|in:3',
            ];
        }

        $validated = $request->validate($rules);

        if($validated){
            if($roleID == 3)
                $ic = $request->get('regNo');
            else
                $ic = $request->get('ICNo');

            $user = new User([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
                'username' => $request->get('username'),
                'contactNo' => $request->get('contactNo'),
                'address' => $request->get('address'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'postalCode' => $request->get('postalCode'),
                'status' => 0,
                'officeNo' => $request->get('officeNo'),
                'ICNo' => $ic,
                'roleID' => $request->get('roleID'),
            ]);

            $user->save();

            // Register for poor people
            if($roleID == 5){
                // Get the poor people details by given ic
                $poor = DB::connection('mysqlSecondConnection')
                ->table('users')
                ->where('users.ICNo', $ic)
                ->first();

                // save to poor table in db
                $poor = new Poor([
                    'disability_type' => $request->get('disType'),
                    'instituition_name' => $poor->instituitionName,
                    'employment_status' => $poor->employmentStatus,
                    'status' => 1,
                    'user_id' => $user->id,
                    'education_level' => $poor->educationLevelID,
                    'volunteer_id' => Auth::user()->id,
                ]);

                $poor->save();
            }

            if($roleID == 1 || $roleID == 2)
                return redirect('/view/' . $roleID )->with('success', 'Pengguna berjaya didaftarkan');
            else
                return redirect('/')->with('success', 'Pengguna berjaya didaftarkan');
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
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function storeAdmin(Request $request)
    {

        $rules = [
            'fname' => 'required',
            'lname' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'username' => 'required|unique:users,username',
            'contactNo' => 'required|unique:users,contactNo',
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'roleID' => 'required|in:1'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $message) {
                return redirect('/viewstaff')->with('error', $message);
            }
        }
        else{
            $user = new User([
                'name' => $request->get('fname') . ' ' . $request->get('lname'),
                'email' => $request->get('email'),
                'password' => Hash::make($request->get('password')),
                'username' => $request->get('username'),
                'contactNo' => $request->get('contactNo'),
                'address' => $request->get('address'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'postalCode' => $request->get('postalCode'),
                'status' => 1,
                'officeNo' => $request->get('officeNo'),
                'ICNo' => $request->get('ICNo'),
                'roleID' => $request->get('roleID'),
            ]);

            $user->save();

            return redirect('/login')->with('success', 'Login with your account');
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        //
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
        $user = User::where([
            ['id', $id],
            ['status', 1]
        ])->first();

        if(Auth::check()){
            return view('users.edit', compact('user'));
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
            'ICNo' => 'required|unique:users,ICNo,' . $id,
            'email' => 'required|unique:users,email,'. $id,
            'username' => 'required|unique:users,username,'. $id,
            'contactNo' => 'required|unique:users,contactNo,'. $id,
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'roleID' => 'required|in:2,4'
        ];

        $validated = $request->validate($rules);

        if($validated){
            DB::table('users')
                ->where('id', $id)
                ->update([
                    'name' => $request->get('name'),
                    'email' => $request->get('email'),
                    'username' => $request->get('username'),
                    'contactNo' => $request->get('contactNo'),
                    'address' => $request->get('address'),
                    'state' => $request->get('state'),
                    'city' => $request->get('city'),
                    'postalCode' => $request->get('postalCode'),
                    'status' => 1,
                    'officeNo' => $request->get('officeNo'),
                    'ICNo' => $request->get('ICNo'),
                    'roleID' => $request->get('roleID'),
                ]);

            return redirect('/view/' . $request->get('roleID'))->with('success', 'Data berjaya dikemaskini');
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
        $result = DB::table('users')
            ->where('id', $id)
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

    public function getUsersDatatable(Request $request)
    {

        if(request()->ajax()){
            $rid = $request->rid;

            $selectedUsers = DB::table('users')
            ->where([
                ['users.status', 1],
                ['users.roleID', $rid],
            ])
            ->orderBy('users.username', 'asc')
            ->get();

            if(isset($selectedUsers)){

                $table = Datatables::of($selectedUsers);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';

                    if(Auth::user()->id == $row->id)
                        $btn = $btn . '<a href="/edituser/' . $row->id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                    else
                        $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';

                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }
            
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyUser(Request $request)
    {   

        // validate the user data
        $request->validate([
            'username' => "required",
            'password' => "required",
        ]);

        // Attempt to log user in
        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){
            return redirect('/');
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->withErrors(['message' => 'Nama Pengguna atau Kata Laluan Tidak Sah']);

    }

    public function logout()
    {
        Auth::logout();
        return redirect('/login')->with('success', 'Berjaya Log Keluar');
    }
}
