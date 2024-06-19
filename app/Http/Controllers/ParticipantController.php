<?php

namespace App\Http\Controllers;

use App\Mail\NotifyParticipant;
use App\Models\Program;
use App\Models\Program_Spec;
use App\Models\Participant;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportParticipant;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;

class ParticipantController extends Controller
{

    // Function to display list of program or participants 
    public function index(){
        if(Auth::check()){

            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;
            
            if()

        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    // Function to display create participants view
    public function create($id){

        if(Auth::check()){

            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            $program = Program::with('organization')
            ->where([
                ['program_id', $id],
                ['status', 1],
            ])
            ->select(
                '*',
                'description->desc as description',
                'description->reason as reason'
            )
            ->first();

            $program->start_date = DateController::parseDate($program->start_date);
            $program->start_time = DateController::formatTime($program->start_time);
            $program->end_date = DateController::parseDate($program->end_date);
            $program->end_time = DateController::formatTime($program->end_time);
            $program->close_date = DateController::parseDate($program->close_date);

            $volLimit = Program::where([
                ['program_id', $id],
            ])
            ->with(['programSpecs' => function ($query) {
                $query->where('user_type_id', 2);
            }])
            ->first();       

            $poorLimit = Program::where([
                ['program_id', $id],
            ])
            ->with(['programSpecs' => function ($query) {
                $query->where('user_type_id', 3);
            }])
            ->first();

            $participantExist = Participant::where([
                ['user_id', $userID],
                ['status', 1]
            ])
            ->with(['programs' => function ($query) {
                $query->where('program_id', $id);
            }])
            ->count();

            $volRemain = $volLimit->programSpecs[0]->qty_limit - $volLimit->programSpecs[0]->qty_enrolled;
            $poorRemain = $poorLimit->programSpecs[0]->qty_limit - $poorLimit->programSpecs[0]->qty_enrolled;

            return view('participants.add', compact('program', 'volLimit', 'poorLimit', 'volRemain', 'poorRemain', 'participantExist', 'roleID', 'userID'));
            
        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);
    }

    // Function to store participants details
    public function store(Request $request){

        $programID = $request->get('programID');
        $userType = $request->get("button_id");
        $userID = Auth::user()->id;

        if(isset($userType) && isset($programID)){
            $participant = new participant([
                'user_type_id' => $userType,
                'program_id' => $programID,
                'user_id' => $userID,
                'status' => 1,
            ]);

            $result = $participant->save();

            if($result){
                $updateEnrolled = Program_Spec::where([
                    ['program_id', $programID],
                    ['user_type_id', $userType],
                ])
                ->increment('qty_enrolled', 1);

                $this->notifyUser($programID, $userID, 1);

                return redirect('/viewallprograms')->with('success', 'Berjaya didaftarkan');
            }

        }

        return redirect('/viewallprograms')->withErrors(['message' => "Pendaftaran tidak berjaya"]);

    }

    // Function to remove user from participant
    public function dismiss(Request $request){
        //

        $programID = $request->get('selectedID');
        $userID = Auth::user()->id;
        $update = 0;

        if(isset($programID) && isset($userID)){
            $update = Program_Spec::where('program_specs.program_id', $programID)
            ->join('participants as p', 'p.user_type_id', '=', 'program_specs.user_type_id')
            ->where([
                ['p.user_id', $userID],
                ['p.status', 1],
                ['p.program_id', $programID],
            ])
            ->decrement('program_specs.qty_enrolled', 1);
        }

        if($update){

            $result = Participant::where([
                ['status', 1],
                ['program_id', $programID],
            ])
            ->update([
                'status' => 0,
            ]);    

            if($result){

                $this->notifyUser($programID, $userID, 0);

                return redirect()->back()->with('success', 'Berjaya dipadam');
            }
        }

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
    }

    // Email user after add
    public function notifyUser($programID, $userID, $status){

        $program = Program::where('program_id', $programID)->select('programs.approved_at', 'programs.name')->first();

        $user = User::where('id', $userID)->select('email', 'username')->first();

        if($program->approved_at != null){
            $date = explode(" ", $program->approved_at);
            $program->approved_at = DateController::parseDate($date[0]) . ' ' . DateController::formatTime($date[1]);
        }

        Mail::to($user->email)->send(new NotifyParticipant([
            'name' => $user->username,
            'status' => $status,
            'program' => $program->name,
            'datetime' => $program->approved_at,
        ]));
    }
}
