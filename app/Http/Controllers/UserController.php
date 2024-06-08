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
use App\Exports\ExportUser;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\WelcomeEmail;
use App\Mail\ForgotPasswordEmail;
use App\Mail\NotifyPasswordChange;
use Illuminate\Support\Str;
use Carbon\Carbon;

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

        $role = $request->get('role');

        if($role == 5){
            $number = $request->get('ic');
        }
        else{
            $number = $request->get('ssm');
        }

        if(isset($number)){
            if($role == 5){
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

        if($roleID == 1 || $roleID == 2 || $roleID == 4){
            // store as admin / staff / volunteer
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
            ];
        }
        else if($roleID == 5){
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

            $username = Str::lower(trim($request->get('username')));

            $user = new User([
                'name' => $request->get('name'),
                'email' => $request->get('email'),
                'password' => Hash::make(trim($request->get('password'))),
                'username' => $username,
                'contactNo' => $request->get('contactNo'),
                'address' => $request->get('address'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'postalCode' => $request->get('postalCode'),
                'status' => 0,
                'officeNo' => $request->get('officeNo'),
                'ICNo' => $ic,
                'roleID' => $request->get('roleID'),
                'remember_token' => Str::random(32),
            ]);

            $user->save();

            $this->validateEmail($user);

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
                return redirect('/view/' . $roleID )->with('success', 'Pengguna berjaya didaftarkan. Sila semak emel untuk pengesahan.');
            else
                return redirect('/')->with('success', 'Pengguna berjaya didaftarkan. Sila semak emel untuk pengesahan.');
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
                'password' => Hash::make(trim($request->get('password'))),
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
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request)
    {
        $id = $request->get('uid');

        //
        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users,email,'. $id,
            'username' => 'required|unique:users,username,'. $id,
            'contactNo' => 'required|unique:users,contactNo,'. $id,
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'roleID' => 'required|in:1,2,3,4,5'
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

            if($id == Auth::user()->id){
                return redirect('/viewprofile')->with('success', 'Data berjaya dikemaskini');
            }
            else{
                return redirect('/view/' . $request->get('roleID'))->with('success', 'Data berjaya dikemaskini');
            }

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

            $user = Auth::user();

            if($user->status == 0){
                $user->remember_token = Str::random(32);
                $user->save();

                $this->validateEmail($user);
                return redirect('/login')->with(['success' => 'Sila semak emel untuk pengesahan']);
            }
            else{
                return redirect('/');
            }
        }

        return redirect()->back()
            ->withInput($request->only('username'))
            ->withErrors(['message' => 'Nama Pengguna atau Kata Laluan Tidak Sah']);

    }

    public function logout()
    {
        Auth::logout();
        return redirect('/')->with('success', 'Berjaya Log Keluar');
    }

    // Function to export user info
    public function exportUsers(Request $request){
        
        // Validate the request data
        $rules = [
            'roleID' => 'required',
            'roleName' => 'required',
        ];
        
        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            
            // Is B40/OKU
            if($roleID == 5){
                $filename = "B40_OKU";
            }
            else{
                $filename = $request->get('roleName');
            }
            

            return Excel::download(new ExportUser($roleID), $filename . '-' . time() . '.xlsx');
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

    public function indexProfile(){
        
        if(Auth::check()){
            $id = Auth::user()->id;
            $roleID = Auth::user()->roleID;

            // Is B40 / OKU
            if($roleID == 5){
                $user = User::where([
                    ['status', 1],
                    ['id', $id],
                ])
                ->with(["poor", "poor.disabilityType", "poor.educationLevel"])
                ->first();
            }
            else{
                $user = User::where([
                    ['status', 1],
                    ['id', $id],
                ])
                ->first();
            }

            return view('users.profile', compact('user'));
        }
        
        return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        
    }
        
    public function validateEmail($user){
        Mail::to($user->email)->send(new WelcomeEmail([
            'name' => $user->username,
            'remember_token' => $user->remember_token
        ]));
    }

    public function confirmEmail(Request $request){
        $token = $request->query('token');

        $user = User::where([
            ['remember_token', $token],
            ['status', 0],
        ])
        ->first();

        if($user){
            $now = Carbon::now();
            $user->email_verified_at = $now;
            $user->remember_token = null;
            $user->status = 1;
            
            $user->save();

            return redirect('/login')->with(['success' => "Emel pengesahan berjaya"]);
        }

        return redirect('/login')->with(['error' => "Emel pengesahan tidak berjaya"], 400);

    }

    public function changePasswordEmail(Request $request){

        $username = $request->get('reset-username');

        $user = User::where([
            ['username', $username],
            ['status', 1],
        ])
        ->first();

        if($user){
            $user->remember_token = Str::random(32);
            $user->save();

            Mail::to($user->email)->send(new ForgotPasswordEmail([
                'name' => $user->username,
                'remember_token' => $user->remember_token
            ]));

            return redirect('/login')->with(['success' => 'Sila semak emel untuk tukar kata laluan']);
        }

        return redirect('/login')->with(['error' => "Nama pengguna tidak wujud"], 400);
        
    }

    public function resetPassword(){

        $username = Auth::user()->username;

        $user = User::where([
            ['username', $username],
            ['status', 1],
        ])
        ->first();

        if($user){
            $user->remember_token = Str::random(32);
            $user->save();

            return redirect('/set-password?token=' . $user->remember_token);
        }

        return redirect('/login')->with(['error' => "Nama pengguna tidak wujud"], 400);
        
    }

    public function indexChangePassword(Request $request){
        $token = $request->query('token');

        $username = User::where([
            ['remember_token', $token],
        ])
        ->value("username");

        if($username){
            return view("auths.forgot", compact('username', 'token'));
        }

        return redirect('/login')->with(['error' => "Pertukaran kata laluan tidak berjaya"], 400);

    }

    public function changePassword(Request $request){
        
        if(Auth::check()){
            $rules = [
                'old-password' => "required",
                'password' => "required",
                'password2' => "required"
            ];
        }
        else{
            $rules = [
                'password' => "required",
                'password2' => "required"
            ];
        }

        $validated = $request->validate($rules);

        if($validated){

            $password = trim($request->get('password'));
            $password2 = trim($request->get('password2'));
            $username = $request->get('username');
            $token = $request->get('token');

            if(Auth::check()){
                $oldPwd = trim($request->get('old-password'));

                if (!Hash::check($oldPwd, Auth::user()->password)) {
                    return redirect('/set-password?token=' . $token)->withErrors(['message' => 'Pertukaran kata laluan tidak berjaya']);
                }
            }

            if($password == $password2){

                $user = User::where([
                    ['username', $username],
                    ['remember_token', $token]
                ])
                ->first();

                if($user){
                    $user->password = Hash::make($password);
                    $user->remember_token = null;
                    $user->status = 1;
                    
                    $user->save();

                    DB::table('password_resets')->insert([
                        'email' => $user->email, 
                        'token' => $token,
                        'created_at' => now()
                    ]);

                    $this->notifyChangePassword($user);

                    Auth::logout();
        
                    return redirect('/login')->with(['success' => "Pertukaran kata laluan berjaya"]);
                }
     
            }
        }

        return redirect('/set-password?token=' . $token)->withErrors(['message' => 'Pertukaran kata laluan tidak berjaya']);

    }

    public function notifyChangePassword($user){

        $now = Carbon::now();

        // Translation arrays
        $days = [
            'Sunday' => 'Ahad',
            'Monday' => 'Isnin',
            'Tuesday' => 'Selasa',
            'Wednesday' => 'Rabu',
            'Thursday' => 'Khamis',
            'Friday' => 'Jumaat',
            'Saturday' => 'Sabtu',
        ];

        $months = [
            'January' => 'Jan',
            'February' => 'Feb',
            'March' => 'Mac',
            'April' => 'Apr',
            'May' => 'Mei',
            'June' => 'Jun',
            'July' => 'Jul',
            'August' => 'Ogo',
            'September' => 'Sep',
            'October' => 'Okt',
            'November' => 'Nov',
            'December' => 'Dis',
        ];

        $formattedDateTime = $now->format('l, j F Y H:i:s');

        // Replace English day and month names with Malay equivalents
        $dayOfWeek = $days[$now->format('l')];
        $month = $months[$now->format('F')];

        // Create the final formatted date-time string
        $formattedDateTime = str_replace([$now->format('l'), $now->format('F')], [$dayOfWeek, $month], $formattedDateTime);

        Mail::to($user->email)->send(new NotifyPasswordChange([
            'name' => $user->username,
            'datetime' => $formattedDateTime
        ]));

    }

}
