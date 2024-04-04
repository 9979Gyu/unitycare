<?php

namespace App\Http\Controllers;

use App\Models\Member;
use Facade\FlareClient\View;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

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
        return view('users.add');
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        //
        
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        //
        $request->validate([
            'name' => 'required',
            'email' => 'required',
            'password' => 'required',
            'username' => 'required',
            'contactNo' => 'required',
            'address' => 'required',
            'state' => 'required',
            'city' => 'required',
            'postalCode' => 'required',
            'status' => 'required',
            'roleID' => 'required|in:1,2,3,4,5'
        ]);

        $user = new User([
            'name' => $request->get('name'),
            'email' => $request->get('email'),
            'password' => $request->get('password'),
            'username' => $request->get('username'),
            'contactNo' => $request->get('contactNo'),
            'address' => $request->get('address'),
            'state' => $request->get('state'),
            'city' => $request->get('city'),
            'postalCode' => $request->get('postalCode'),
            'status' => $request->get('status'),
            'officeNo' => $request->get('officeNo'),
            'ICNo' => $request->get('ICNo'),
            'roleID' => $request->get('roleID'),
        ]);

        $user->save();

        return redirect('/users')->with('success', 'user is added');
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
}
