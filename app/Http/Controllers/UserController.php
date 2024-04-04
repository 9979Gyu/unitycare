<?php

namespace App\Http\Controllers;

use App\Models\User;
use Facade\FlareClient\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use DataTables;
use Illuminate\Support\Facades\Hash;

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
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function login()
    {
        //
        return view('users.login');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        return view('users.add');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function register()
    {
        //
        return view('users.adminReg');
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
            'roleID' => 'required|in:1,2,3,4,5'
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();

            // Option 1: Loop through all errors and display them
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

            // Option 1: Loop through all errors and display them
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
                    $btn = $btn . '<span class="badge badge-warning"> Edit </span></div>';
                    $btn = $btn . '<span class="badge badge-danger"> Remove </span></div>';

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
        $rules = [
            'password' => 'required',
            'username' => 'required',
        ];

        $validator = Validator::make($request->all(), $rules);

        if ($validator->fails()) {
            $errors = $validator->errors();

            // Option 1: Loop through all errors and display them
            foreach ($errors->all() as $message) {
                return redirect('/login')->with('error', $message);
            }
        }
        else{

            $password = $request->get('password');
            $username = $request->get('username');

            $selectedUser = DB::table('users')
            ->where([
                ['users.username', $username],
            ])
            ->first();

            if(isset($selectedUser)){
                if (Hash::check($password, $selectedUser->password)){
                    if($selectedUser->roleID == 1)
                        return redirect('/viewstaff')->with('success', 'Welcome admin');
                    else
                        return redirect('/welcome');
                }
                else{
                    return redirect('/login')->with('error', 'Wrong username or password');
                }
                
            }

            
        }
    }
}
