<?php

namespace App\Http\Controllers;

use App\Models\User;
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
    public function index()
    {
        //
        $users = User::where('roleID', 2)->get();
        return view('users.index', compact('users'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create($roleNo)
    {
        //
        if($roleNo == 1){
            // show add admin page
            if (Auth::check() && Auth::user()->roleID === 1) {
                // User is logged in and has roleID of 6
                return view('users.adminReg');
            } else {
                // User is not logged in or doesn't have roleID of 6
                echo "You are not authorized to access this page.";
            }
        }
        else if($roleNo == 2){
            // show add staff page
            return view('users.add');
        }
        else if($roleNo == 3){
            // show add enterprise page
        }
        else if($roleNo == 4){
            // show add volunteer page
            return view('volunteers.add');
        }
        else if($roleNo == 5){
            // show add poor people page
        }
        else{
            // show landing page
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

        $rules = [
            'name' => 'required',
            'email' => 'required|unique:users,email',
            'password' => 'required',
            'username' => 'required|unique:users,username',
            'contactNo' => 'required|unique:users,contactNo',
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'roleID' => 'required|in:1,2,3,4,5'
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

            return redirect('/viewstaff')->with('success', 'user is added');
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
        ])->first();
        if($user->roleID == 2){
            return view('users.edit', compact('user'));
        }
        else{
            return view('welcome');
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
            'email' => 'required|unique:users,email,'. $id,
            'username' => 'required|unique:users,username,'. $id,
            'contactNo' => 'required|unique:users,contactNo,'. $id,
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'roleID' => 'required|in:2'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();

            foreach ($errors->all() as $message) {
                return redirect('/viewstaff')->with('error', $message);
            }
        }
        else{

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

            return redirect('/viewstaff')->with('success', 'Information updated');
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
            return redirect('/viewstaff')->with('success', 'Successfully Deleted');
        }
        else{
            return redirect('/viewstaff')->with('error', 'Failed to Delete');
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
                    $btn = $btn . '<a href="/edituser/' . $row->id . '"><span class="badge badge-warning"> Edit </span></a></div>';
                    $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Remove </span></a></div>';

                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }
            
        }

        return view('users.index');
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
