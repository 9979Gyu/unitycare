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
use App\Exports\ExportProgramReport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class ReportController extends Controller
{
    //
    public function indexPrograms(){
        $roleNo = Auth::user()->roleID;

        if($roleNo == 1 || $roleNo == 2){
            $users = User::where([
                ['users.status', 1],
            ])
            ->join('programs', 'programs.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
            )
            ->distinct()
            ->get();
        }
        else{
            $users = User::where([
                ['status', 1],
                ['id', Auth::user()->id]
            ])
            ->select(
                'users.id',
                'users.name',
            )
            ->distinct()
            ->get();
        }

        return view('reports.programs.userIndex', compact('roleNo', 'users'));
    }

    // Function to get list of programs details with it programs spec
    public function getAllProgramsDetailsDatatable(Request $request){
        if($request->ajax()){
            $selectedUser = $request->get('selectedUser');
            $selectedState = $request->get('selectedState');
            $selectedType = $request->get('selectedType');
            $status = $request->get('status');

            if(isset($selectedUser)){

                $query = Program::where([
                    ['programs.status', $status]
                ])
                ->join('types', 'types.type_id', '=', 'programs.type_id')
                ->join('users as u', 'u.id', '=', 'programs.user_id')
                ->join('program_specs as ps', 'ps.program_id', '=', 'programs.program_id')
                ->where([
                    ['u.status', 1],
                    ['u.id', $selectedUser],
                ]);

                // user select radio button other than Active
                if($selectedState != 3){
                    $query->where('programs.approved_status', $selectedState);
                }

                // user select option other than Semua Jenis
                if($selectedType != 3){
                    $query->where('types.type_id', $selectedType);
                }

                $selectedPrograms = $query->select(
                    'programs.*',
                    'types.name as typename',
                    'u.name as username',
                    'u.email as useremail',
                    'u.contactNo as usercontact',
                    'ps.*'
                )
                ->orderBy('programs.updated_at', 'desc')
                ->get();

                // Transform the data but keep it as a collection of objects
                $selectedPrograms->transform(function ($program) {
                    $program->description = json_decode($program->description, true)['desc'] ?? '';
                    $program->address = $program->venue . ', ' . $program->postal_code . 
                    ', ' . $program->city . ', ' . $program->state;

                    $startDate = $program->start_date;
                    $program->start_date = $this->parseDate($startDate);
                    $program->start = $program->start_date . ' ' . $program->start_time;

                    $endDate = $program->end_date;
                    $program->end_date = $this->parseDate($endDate);
                    $program->end = $program->end_date . ' ' . $program->end_time;

                    $closeDate = $program->close_date;
                    $program->close_date = $this->parseDate($closeDate);

                    $program->participant = $program->qty_enrolled . '/' . $program->qty_limit;

                    return $program;
                });

            //     $combinedPrograms = collect();

            //     $selectedPrograms->each(function ($program) use ($combinedPrograms) {
            //         // Check if the program already exists in $combinedPrograms
            //         $existingProgram = $combinedPrograms->where('program_id', $program->program_id)->first();

            //         if ($existingProgram) {
            //             // Add program_specs based on user_type_id
            //             if ($program->user_type_id == 2) { // Volunteer
            //                 $existingProgram->vol_limit = $program->qty_limit;
            //                 $existingProgram->vol_enrolled = $program->qty_enrolled;
            //             } elseif ($program->user_type_id == 3) { // Poor
            //                 $existingProgram->poor_limit = $program->qty_limit;
            //                 $existingProgram->poor_enrolled = $program->qty_enrolled;
            //             }
            //         } else {
            //             // Create a new entry in $combinedPrograms
            //             $combinedPrograms->push([
            //                 'program_id' => $program->program_id,
            //                 'name' => $program->name,
            //                 'status' => $program->status,
            //                 'start_date' => $program->start_date,
            //                 'start_time' => $program->start_time,
            //                 'end_date' => $program->end_date,
            //                 'end_time' => $program->end_time,
            //                 'description' => json_decode($program->description, true)['desc'] ?? '',
            //                 'venue' => $program->venue,
            //                 'type_id' => $program->type_id,
            //                 'user_id' => $program->user_id,
            //                 'approved_by' => $program->approved_by,
            //                 'approved_at' => $program->approved_at,
            //                 'approved_status' => $program->approved_status,
            //                 'created_at' => $program->created_at,
            //                 'updated_at' => $program->updated_at,
            //                 'close_date' => $program->close_date,
            //                 'postal_code' => $program->postal_code,
            //                 'state' => $program->state,
            //                 'city' => $program->city,
            //                 'typename' => $program->typename,
            //                 'username' => $program->username,
            //                 'useremail' => $program->useremail,
            //                 'usercontact' => $program->usercontact,
            //                 'address' => $program->address,
            //                 'start' => $program->start,
            //                 'end' => $program->end,
            //                 'vol_limit' => ($program->user_type_id == 2) ? $program->qty_limit : 0,
            //                 'vol_enrolled' => ($program->user_type_id == 2) ? $program->qty_enrolled : 0,
            //                 'poor_limit' => ($program->user_type_id == 3) ? $program->qty_limit : 0,
            //                 'poor_enrolled' => ($program->user_type_id == 3) ? $program->qty_enrolled : 0,
            //             ]);
            //         }
            //     });

            }

            // dd($combinedPrograms);

            if(isset($selectedPrograms)){

                $table = Datatables::of($selectedPrograms);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';

                    if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                        if($row->approved_status == 1){
                            // User created the program and the program is pending approval
                            if($row->user_id == Auth::user()->id){
                                $btn = $btn . '<a href="/editprogram/' . $row->program_id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                                $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                            }
                            $btn = $btn . '<a class="approveAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-success" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a></div>';
                            $btn = $btn . '<a class="declineAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a></div>';
                        }
                        // The program is declined
                        elseif($row->approved_status == 0){
                            if($row->user_id == Auth::user()->id){
                                $btn = $btn . '<a href="/editprogram/' . $row->program_id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                                $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                            }
                        }
                    }
                    // User is enterprise or volunteer
                    elseif(Auth::user()->roleID == 3 || Auth::user()->roleID == 4){
                        // User created the program and the program is pending approval
                        if(($row->approved_status == 1 || $row->approved_status == 0) && $row->user_id == Auth::user()->id){

                            $btn = $btn . '<a href="/editprogram/' . $row->program_id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                            $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                            
                        }
                        elseif($row->approved_status == 2){
                            $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->program_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                        }
                    }

                    if($row->status == 0){
                        $btn = " ";
                    }

                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }

        return view('reports.programs.userIndex');
        
    }

    // Function to export programs info
    public function exportProgramsWithSpecs(Request $request){
        
        // Validate the request data
        $rules = [
            'statusFilter' => 'required',
            'type' => 'required',
            'organization' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $selectedUser = $request->get('organization');
            $selectedState = $request->get('statusFilter');
            $selectedType = $request->get('type');
            $status = 1;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if($selectedState == 4){
                $status = 0;
            }

            if(isset($selectedUser)){

                $query = Program::where([
                    ['programs.status', $status],
                    ['programs.start_date', '>=', $startDate],
                    ['programs.start_date', '<=', $endDate],
                ])
                ->join('types', 'types.type_id', '=', 'programs.type_id')
                ->join('users as u', 'u.id', '=', 'programs.user_id')
                ->join('users as processed', 'processed.id', '=', 'programs.approved_by')
                ->join('program_specs as ps', 'ps.program_id', '=', 'programs.program_id')
                ->where([
                    ['u.status', 1],
                    ['u.id', $selectedUser],
                ]);

                // user select radio button other than Active
                if($selectedState < 3 ){
                    $query->where('programs.approved_status', $selectedState);
                }

                // user select option other than Semua Jenis
                if($selectedType != 3){
                    $query->where('types.type_id', $selectedType);
                }

                $selectedPrograms = $query->select(
                    'programs.*',
                    'types.name as typename',
                    'u.name as username',
                    'u.email as useremail',
                    'u.contactNo as usercontact',
                    'processed.name as processedname',
                    'processed.email as processedemail',
                    'processed.contactNo as processedcontact',
                    'ps.*'
                )
                ->orderBy('programs.updated_at', 'desc')
                ->get();

                // Transform the data but keep it as a collection of objects
                $selectedPrograms->transform(function ($program) {
                    $program->description = json_decode($program->description, true)['desc'] ?? '';
                    $program->address = $program->venue . ', ' . $program->postal_code . 
                    ', ' . $program->city . ', ' . $program->state;

                    $startDate = $program->start_date;
                    $program->start_date = $this->parseDate($startDate);
                    $program->start = $program->start_date . ' ' . $program->start_time;

                    $endDate = $program->end_date;
                    $program->end_date = $this->parseDate($endDate);
                    $program->end = $program->end_date . ' ' . $program->end_time;

                    $closeDate = $program->close_date;
                    $program->close_date = $this->parseDate($closeDate);

                    $approved_at = explode(' ', $program->approved_at);
                    $program->approved_at = $this->parseDate($approved_at[0]) . ' ' . $approved_at[1];

                    $program->participant = $program->qty_enrolled . '/' . $program->qty_limit;

                    return $program;
                });
            }

            $filename = User::where('id', $selectedUser)->value('name');

            return Excel::download(new ExportProgramReport($selectedPrograms), 
                'Senarai Program (' . $filename . ') - ' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

    // Function to parse format for date
    public function parseDate($olddate){
        try {
            // Parse the date with the specified format
            $date = Carbon::createFromFormat('Y-m-d', $olddate);

            // Set the locale to Malay
            $date->locale('ms');

            // Format the date to 'dddd, D MMMM YYYY' (without time since it's not provided)
            $formattedDate = $date->isoFormat('dddd, D MMMM YYYY');

            return $formattedDate;

        } 
        catch (Exception $e) {
            return $date;
        }
    }
}
