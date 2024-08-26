<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Poor;
use App\Models\Program;
use App\Models\Job_Offer;
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
use App\Mail\ChangeProfileEmail;
use Illuminate\Support\Str;
use Carbon\Carbon;
use DateTime;
use thiagoalessio\TesseractOCR\TesseractOCR;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index($roleNo){
        //
        if(Auth::check()){
            $logRole = Auth::user()->roleID;

            if($logRole == 1 || ($logRole == 2 && $roleNo > 2)){
                $users = User::where('roleID', $roleNo)->get();
                
                $rolename = DB::table('roles')
                    ->where('roleID', $users[0]->roleID)
                    ->value("name");

                return view('users.index', compact('users', 'rolename', 'roleNo'));
            }

        }
        return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create(Request $request){
        $role = $request->query('user');

        switch ($role) {
            case 'Kakitangan':
                $roleNo = 2; 
                break;
            case 'Syarikat':
                $roleNo = 3;
                return view('users.enterprise.add', compact('roleNo'));
            case 'Sukarelawan':
                $roleNo = 4; 
                break;
            case 'B40 / OKU':
                $roleNo = 5; 
                break;
            default:
                $roleNo = 0;
                break;
        };

        if($roleNo == 0){
            return redirect('/login')->withErrors(['message' => 'Tindakan tidak dibenarkan']);
        }

        // Default view if role is not matched
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

    // Function to extract icno from image
    public function extractText(Request $request){

        if ($request->hasFile('image')){
            // Handle the uploaded file
            $imagePath = $request->file('image')->storeAs('images', 'ic_image', 'public');

            if (!$imagePath) {
                return redirect()->back()->withErrors(['message' => 'Pengesanan teks tidak berjaya']);
            }
    
            // Generate the full path to the file
            $fullPath = storage_path('app/public/' . $imagePath);
    
            if (!file_exists($fullPath)) {
                return redirect()->back()->withErrors(['message' => 'Pengesanan teks tidak berjaya']);
            }
    
            // Create a new instance of TesseractOCR
            $tesseract = new TesseractOCR($fullPath);
    
            // Set the language of the text in the image
            $tesseract->lang('eng');
    
            // Get the text from the image
            $text = $tesseract->run();
    
            // Recognize IC number from the text
            if (preg_match('/\b\d{6}-\d{2}-\d{4}\b/', $text, $matches)) {
                $number = $matches[0];
                
                // Remove the '-' from the IC number
                $number = str_replace('-', '', $number);
    
                // Return the extracted text as JSON response
                return redirect()->back()->with(['extractedText' => $number]);
            } 
        }
        
        return response()->json(['message' => 'Text extraction failed'], 400);
        
    }

    // Function to search poor or enterprise from external databases
    public function checkUser(Request $request){

        $result = 0;

        $roleID = $request->get('roleID');

        if($roleID == 5){
            $number = $request->get('ic');
        }
        else{
            $number = $request->get('ssmRegNo');
        }

        if(isset($number)){
            if($roleID == 5){
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

                if($roleID == 5){
                    $rules = [
                        'ic' => 'required|unique:users,ICNo',
                    ];
                }
                else{
                    $rules = [
                        'ssmRegNo' => 'required|unique:users,ICNo',
                    ];
                }

                $validated = $request->validate($rules);

                if($validated){
 
                    $password = Str::random(8);

                    $user = new User([
                        'name' => $result->name,
                        'email' => $result->email,
                        'contactNo' => $result->contactNo,
                        'address' => $result->address,
                        'state' => $result->state,
                        'city' => $result->city,
                        'postalCode' => $result->postcode,
                        'status' => 0,
                        'ICNo' => $number,
                        'roleID' => $roleID,
                        'remember_token' => Str::random(32),
                        'username' => $result->name,
                        'password' => Hash::make($password),
                        'image' => 'default_image.png',
                    ]);

                    if($roleID == 3){
                        $user->sector_id = $result->sectorID;
                        $user->officeNo = $result->officeNo ? $result->officeNo : null;
                    }

                    $user->save();
                    $user->password = $password;

                    $this->validateEmail($user);

                    // Register for poor people
                    if($roleID == 5){
                        // save to poor table in db
                        $poor = new Poor([
                            'disability_type' => $result->disabilityType,
                            'instituition_name' => $result->instituitionName,
                            'employment_status' => $result->employmentStatus,
                            'status' => 1,
                            'user_id' => $user->id,
                            'education_level' => $result->educationLevelID,
                            'volunteer_id' => Auth::user()->id,
                        ]);

                        $poor->save();
                    }

                    return redirect('/')->with(["success" => "Pengguna berjaya didaftarkan. Sila semak emel untuk pengesahan."]);
                }
                
            }
        }

        if($roleID == 5){
            return redirect('/createspecial')->withErrors(["message" => "Nombor pengenalan telah diguna"]);
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
    public function store(Request $request){

        $roleID = $request->get('roleID');

        if($roleID == 1 || $roleID == 2 || $roleID == 4){
            // store as admin / staff / volunteer
            $rules = [
                'name' => 'required',
                'ICNo' => 'required|unique:users,ICNo',
                'email' => 'required|unique:users,email',
                'username' => 'required|unique:users,username',
                'contactNo' => 'required|unique:users,contactNo',
                'address' => 'required',
                'state' => 'required',
                'city' => 'required',
                'postalCode' => 'required',
                'roleID' => 'required|in:1,2,4,5',
            ];
        }

        $validated = $request->validate($rules);

        if($validated){

            $username = Str::lower(trim($request->get('username')));
            $email = Str::lower(trim($request->get('email')));
            $password = Str::random(8);
            $ic = $request->get('ICNo');

            $firstYear = substr($ic, 0, 1);
            $secondYear = substr($ic, 1, 1);
            $yy = substr($ic, 0, 2);
            $mm = substr($ic, 2, 2);
            $dd = substr($ic, 4, 2);

            if($firstYear <= 2){
                $birthDateStr = "20$yy-$mm-$dd";
            }
            else{
                $birthDateStr = "19$yy-$mm-$dd";
            }

            // Convert birthDateStr to a DateTime object
            $birthDate = new DateTime($birthDateStr);

            // Calculate 12 years ago from today
            $eighteenYearsAgo = new DateTime('-12 years');

            if ($birthDate > $eighteenYearsAgo) {
                return redirect()->back()->withInput($request->all())->withErrors(['message' => 'Maaf. Umur pengguna tidak sesuai']);
            }

            $user = new User([
                'name' => ucwords(trim($request->get('name'))),
                'email' => $email,
                'password' => Hash::make($password),
                'username' => $username,
                'contactNo' => $request->get('contactNo'),
                'address' => ucwords(trim($request->get('address'))),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'postalCode' => $request->get('postalCode'),
                'status' => 0,
                'officeNo' => $request->get('officeNo'),
                'ICNo' => $ic,
                'roleID' => $request->get('roleID'),
                'remember_token' => Str::random(32),
                'image' => 'default_image.png',
            ]);

            $user->save();

            $user->password = $password;

            $this->validateEmail($user);

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
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id){
        //
        $user = User::where([
            ['id', $id],
            ['status', 1]
        ])->first();

        if(Auth::check() && Auth::user()->roleID == 1){
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
    public function update(Request $request){
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

            Auth::logout();
            
            $remember_token = Str::random(32);
            $email = Str::lower(trim($request->get('email')));
            $name = Str::lower(trim($request->get('name')));

            $update = User::where('id', $id)->update(['remember_token' => $remember_token]);

            if($update){

                $old_user = User::where('id', $id)->select('username', 'email', 'image')->first();
                $poorResume = Poor::where('user_id', $id)->value('resume');

                $data = [
                    'email' => $email,
                    'username' => Str::lower(trim($request->get('username'))),
                    'contactNo' => $request->get('contactNo'),
                    'address' => ucwords(trim($request->get('address'))),
                    'state' => $request->get('state'),
                    'city' => $request->get('city'),
                    'postalCode' => $request->get('postalCode'),
                    'status' => 0,
                    'officeNo' => $request->get('officeNo'),
                    'remember_token' => $remember_token,
                ];

                if ($request->hasFile('image')) {
                    $file = $request->file('image');
                
                    // Check if the file upload was successful
                    if ($file->isValid()) {
                        $filename = $name . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('public/user_images'), $filename);
                        $data['image'] = $filename;
                    }
                } 
                else {
                    $data['image'] = $old_user->image;
                }

                if ($request->hasFile('resume')) {
                    $file = $request->file('resume');
                
                    // Check if the file upload was successful
                    if ($file->isValid()) {
                        $filename = 'resume_' . $name . '.' . $file->getClientOriginalExtension();
                        $file->move(public_path('public/attachments'), $filename);
                        $data['resume'] = $filename;
                    }
                } 
                else {
                    $data['resume'] = $poorResume;
                }

    
                Mail::to($old_user->email)->send(new ChangeProfileEmail($data, $remember_token, $old_user->username));
    
                return redirect('/')->with('success', 'Sila semak emel untuk mengesahkan perubahan.');
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
    public function destroy(Request $request){
        //
        $id = $request->get('selectedID');
        $result = DB::table('users')
            ->where('id', $id)
            ->update([
                'status' => 0,
            ]);

        if($result){

            Program::where('user_id', $id)
            ->update([
                'status' => 0,
            ]);

            Job_Offer::where('user_id', $id)
            ->update([
                'status' => 0,
            ]);

            return redirect()->back()->with('success', 'Berjaya dipadam');
        }
        else{
            return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
        }
    }

    public function getUsersDatatable(Request $request){

        if ($request->ajax()) {
            $rid = $request->rid;

            $selectedUsers = DB::table('users')
                ->where([
                    ['users.status', 1],
                    ['users.roleID', $rid],
                ])
                ->orderBy('users.username', 'asc')
                ->get();

            if ($selectedUsers->isEmpty()) {
                // No users found
                return response()->json(['message' => 'No users found'], 500);
            }

            $table = Datatables::of($selectedUsers);

            $table->addColumn('action', function ($row) {
                $token = csrf_token();
                $btn = '<div class="d-flex justify-content-center">';

                if (Auth::user()->id == $row->id) {
                    $btn .= '<a href="/edituser/' . $row->id . '"><span class="badge badge-warning"> Kemaskini </span></a>';
                } else {
                    $btn .= '<a class="deleteAnchor" href="#" id="' . $row->id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                }
                $btn .= '</div>';

                return $btn;
            });

            $table->rawColumns(['action']);
            return $table->make(true);
        }
    }


    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function verifyUser(Request $request){   
        // validate the user data
        $request->validate([
            'username' => "required",
            'password' => "required",
        ]);

        // Attempt to log user in
        if(Auth::attempt(['username' => $request->username, 'password' => $request->password])){

            $user = Auth::user();

            if($user->status == 0){

                Auth::logout();

                $user->remember_token = Str::random(32);
                $user->save();

                $user->password = "(Kata laluan terkini akaun anda)";

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
            'password' => $user->password,
            'remember_token' => $user->remember_token,
            'email' => $user->email,
        ]));
    }

    public function confirmEmail(Request $request){
        $token = $request->query('token');
        $email = $request->query('email');

        $user = User::where([
            ['remember_token', $token],
        ])
        ->first();

        if($user){

            $now = Carbon::now();
            $user->email_verified_at = $now;
            $user->remember_token = NULL;
            $user->email = $email;
            $user->status = 1;
            
            $user->save();
            
            return redirect('/login')->with(['success' => "Emel pengesahan berjaya"]);
            
        }

        return redirect('/login')->withErrors(['message' => 'Emel pengesahan tidak berjaya']);

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
    
    // Function to update user detail after verify email
    public function confirmProfile(Request $request){
        $token = $request->query('token');
        $data = $request->query('data');

        $user = User::where([
            ['remember_token', $token],
        ])
        ->first();

        if($user){

            // Decode JSON-encoded $data to array
            $data = json_decode(urldecode($data), true);

            if($user->email != $data['email']){
                $user->status = 0;
            }

            $user->email_verified_at = now();
            $user->remember_token = null;
            $user->email = $data['email'];
            $user->username = $data['username'];
            $user->contactNo = $data['contactNo'];
            $user->address = $data['address'];
            $user->state = $data['state'];
            $user->city = $data['city'];
            $user->postalCode = $data['postalCode'];
            $user->officeNo = $data['officeNo'];
            $user->image = $data['image'];

            $user->save();

            Poor::where('user_id', $user->id)->update(['resume' => $data['resume']]);
            
            return redirect('/login')->with(['success' => "Profil berjaya dikemaskini"]);
            
        }

        return redirect('/login')->withErrors(['message' => 'Emel pengesahan tidak berjaya']);

    }

}
