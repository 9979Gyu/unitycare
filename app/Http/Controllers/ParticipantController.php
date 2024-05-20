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

class ParticipantController extends Controller
{
    //
    public function index(){

    }

    public function create($id){

        if(Auth::check()){
            $program = Program::with('user')
            ->where([
                ['program_id', $id],
                ['status', 1],
                ['approved_status', 2]
            ])
            ->select(
                '*',
                'description->desc as description',
                'description->reason as reason'
            )
            ->first();

            $volLimit = DB::table("programs")
            ->join('program_specs as ps', 'ps.program_id', '=', 'programs.program_id')
            ->where([
                ['programs.program_id', $id],
                ['programs.status', 1],
                ['programs.approved_status', 2],
                ['ps.user_type_id', 2],
            ])
            ->first();

            $poorLimit = DB::table("programs")
            ->join('program_specs as ps', 'ps.program_id', '=', 'programs.program_id')
            ->where([
                ['programs.program_id', $id],
                ['programs.status', 1],
                ['programs.approved_status', 2],
                ['ps.user_type_id', 3],
            ])
            ->first();

            $participantExist = DB::table('participants')
            ->join('programs as p', 'p.program_id', '=', 'participants.program_id')
            ->where([
                ['p.program_id', $id],
                ['participants.status', 1],
                ['participants.user_id', Auth::user()->id]
            ])
            ->count();

            $volRemain = $volLimit->qty_limit - $volLimit->qty_enrolled;
            $poorRemain = $poorLimit->qty_limit - $poorLimit->qty_enrolled;

            return view('participants.add', compact('program', 'volLimit', 'poorLimit', 'volRemain', 'poorRemain', 'participantExist'));
            
        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk untuk melayari halaman']);

    }

    public function store(Request $request){
    
        $userType = $request->get("button_id");
        $programID = $request->get("program_id");
        $uid = Auth::user()->id;

        if(isset($userType) && isset($programID)){
            $participant = new participant([
                'user_type_id' => $userType,
                'program_id' => $programID,
                'user_id' => $uid,
                'status' => 1,
            ]);

            $result = $participant->save();

            if($result){
                $updateEnrolled = DB::table('program_specs')
                ->where([
                    ['program_id', $programID],
                    ['user_type_id', $userType],
                ])
                ->increment('qty_enrolled', 1);

                return redirect('/viewprogram')->with('success', 'Berjaya didaftarkan');
            }
            else{
                return redirect('/viewprogram')->withErrors(['message' => "Pendaftaran tidak berjaya"]);
            }
        }
    }

    public function dismiss(Request $request)
    {
        //
        $update = DB::table('program_specs')
        ->join('participants as p', 'p.user_type_id', '=', 'program_specs.user_type_id')
        ->where([
            ['p.user_id', Auth::user()->id],
            ['p.status', 1],
            ['program_specs.program_id', $request->selectedID],
            ['p.program_id', $request->selectedID],
        ])
        ->decrement('program_specs.qty_enrolled', 1);

        if($update){

            $result = DB::table('participants')
            ->where([
                ['status', 1],
                ['program_id', $request->selectedID],
            ])
            ->update([
                'status' => 0,
            ]);    

            if($result)
                return redirect()->back()->with('success', 'Berjaya dipadam');
        }

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
    }
}
