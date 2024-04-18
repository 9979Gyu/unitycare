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
                'approved_status' => 0,
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
}
