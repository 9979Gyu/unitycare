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
use App\Exports\ExportProgram;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyJoinEmail;

class ProgramController1 extends Controller
{

    public static function getProgramsByApprovedStatus(){

        $roleNo = 0;

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
        }

        $allPrograms = Program::where('programs.status', 1)
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->with('organization')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        $approvedPrograms = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 2]
        ])
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        $pendingPrograms = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 1]
        ])
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        $declinedPrograms = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 0]
        ])
        ->join('users', 'programs.user_id', '=', 'users.id')
        ->select(
            'programs.*', 
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail'
        )
        ->get();

        if($roleNo == 5){
            $programs = $approvedPrograms;
        }
        else{
            $programs = $allPrograms;
        }

        return $programs;

    }


    // Return the program details based on given program id
    public function getProgramById(Request $request)
    {
        if(request()->ajax()){
            $pid = $request->pid;

            $participants = Program_Spec::where('program_id', $pid)
            ->get();

            // Join programs, program_spec, and users tables (using relationships)
            $program = DB::table('programs')
            ->join('users as u', 'u.id', '=', 'programs.user_id')
            ->where([
                ['programs.status', 1],
                ['programs.program_id', $pid],
            ])
            ->select([
                'programs.*',
                'u.name as username',
            ])
            ->first();

            $data = [
                'program' => $program,
                'participants' => $participants,
            ];

            return response()->json($data);
        }
    }

    public function getUpdatedPrograms(){
        $allPrograms = Program::where([
            ['programs.status', 1],
        ])
        ->join('users', 'id', '=', 'user_id')
        ->join('types', 'types.type_id', '=', 'programs.type_id')
        ->select(
            'programs.*', 
            'programs.description->desc as description',
            'programs.description->reason as reason',
            'users.id as userid',
            'users.name as username', 
            'users.contactNo as contact_no', 
            'users.email as useremail',
            'types.name as typename'
        )
        ->orderBy('programs.updated_at', 'desc')
        ->get();

        $enrolled = Participant::where([
            ['user_id', Auth::user()->id],
            ['status', 1],
        ])
        ->select('program_id as pid')
        ->get();

        return response()->json([
            'allPrograms' => $allPrograms,
            'enrolled' => $enrolled
        ]);        
    }

    // Function to export program info
    public function exportPrograms(Request $request){
        
        // Validate the request data
        $rules = [
            'roleID' => 'required',
            'type' => 'required',
            'statusFilter' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            $state = $request->get('statusFilter');
            $type = $request->get('type');
            $status = 1;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            // User select radio dipadam
            if($state == 4){
                $status = 0;
            }

            if(isset($roleID) && isset($state) && isset($type) && isset($status)){

                $query = Program::where([
                    ['programs.status', $status],
                    ['programs.start_date', '>=', $startDate],
                    ['programs.start_date', '<=', $endDate],
                ])
                ->join('users', 'users.id', '=', 'programs.user_id')
                ->join('types', 'types.type_id', '=', 'programs.type_id')
                ->join('program_specs as ps1', 'ps1.program_id', '=', 'programs.program_id')
                ->where('ps1.user_type_id', 2)
                ->join('program_specs as ps2', 'ps2.program_id', '=', 'programs.program_id')
                ->where('ps2.user_type_id', 3);

                if($state != 3) {
                    $query->where('programs.approved_status', $state);
                }

                if($type != 3) {
                    $query->where('programs.type_id', $type);
                }

                $selectedPrograms = $query->select(
                    'programs.*',
                    'types.name as typename',
                    'users.name as username',
                    'users.email as useremail',
                    'users.contactNo as usercontact',
                    'ps1.qty_limit as vol_qty_limit',
                    'ps2.qty_limit as poor_qty_limit'
                )->orderBy('programs.updated_at', 'desc')
                ->get();

                // Transform the data but keep it as a collection of objects
                $selectedPrograms->transform(function ($program) {
                    $program->description = json_decode($program->description, true)['desc'] ?? '';

                    $program->address = $program->venue . ', ' . $program->postal_code . 
                    ', ' . $program->city . ', ' . $program->state;

                    $program->vol = 'Sukarelawan: ' . $program->vol_qty_limit . ' orang';
                    $program->poor = 'B40/OKU: ' . $program->poor_qty_limit . ' orang';

                    $startDate = $program->start_date;
                    $program->start_date = DateController::parseDate($startDate);
                    $program->start = $program->start_date . ' ' . $program->start_time;

                    $endDate = $program->end_date;
                    $program->end_date = DateController::parseDate($endDate);
                    $program->end = $program->end_date . ' ' . $program->end_time;

                    $closeDate = $program->close_date;
                    $program->close_date = DateController::parseDate($closeDate);

                    return $program;
                });

            }
            
            return Excel::download(new ExportProgram($selectedPrograms), 
                'Programs-' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }
}
