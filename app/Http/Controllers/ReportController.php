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
    // Function to view list of offer
    public function indexOffers(){
        $roleNo = Auth::user()->roleID;

        if($roleNo == 1 || $roleNo == 2){
            $users = User::where([
                ['users.status', 1],
            ])
            ->join('job_offers as offers', 'offers.user_id', '=', 'users.id')
            ->select(
                'users.id',
                'users.name',
            )
            ->orderBy('users.name')
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
            ->orderBy('users.name')
            ->distinct()
            ->get();
        }

        return view('reports.offers.index', compact('roleNo', 'users'));

    }

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
            ->orderBy('users.name')
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
            ->orderBy('users.name')
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
                ->join('program_specs as ps1', 'ps1.program_id', '=', 'programs.program_id')
                ->where('ps1.user_type_id', 2)
                ->join('program_specs as ps2', 'ps2.program_id', '=', 'programs.program_id')
                ->where('ps2.user_type_id', 3)
                ->leftJoin('users as processed', function($join) {
                    $join->on('processed.id', '=', 'programs.approved_by')
                         ->whereNotNull('programs.approved_by');
                })
                ->where([
                    ['u.status', 1],
                    ['u.id', $selectedUser],
                ]);

                // user select radio button other than Active
                if($selectedState != 3){
                    $query->where('programs.approved_status', $selectedState);
                }

                // user select option other than Semua Jenis
                if($selectedType == 'vol'){
                    $query->where('types.type_id', 1);
                }
                elseif($selectedType == 'skill'){
                    $query->where('types.type_id', 2);
                }
                elseif($selectedType != 'all'){
                    $query->where('programs.program_id', $selectedType);
                }

                $selectedPrograms = $query->select(
                    'programs.*',
                    'types.name as typename',
                    'u.name as username',
                    'u.email as useremail',
                    'u.contactNo as usercontact',
                    'ps1.qty_limit as vol_limit',
                    'ps1.qty_enrolled as vol_enrolled',
                    'ps2.qty_limit as poor_limit',
                    'ps2.qty_enrolled as poor_enrolled',
                    'processed.name as processedname',
                    'processed.email as processedemail',
                )
                ->orderBy('programs.updated_at', 'desc')
                ->get();

                // Transform the data but keep it as a collection of objects
                $selectedPrograms->transform(function ($program) {

                    $program->reason = json_decode($program->description, true)['reason'] ?? '';
                    $program->description = json_decode($program->description, true)['desc'] ?? '';

                    if($program->approved_at != null){
                        $approved_at = explode(' ', $program->approved_at);
                        $program->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];
                    }

                    if($program->approved_status == 0){
                        $approval = "Ditolak";
                    }
                    elseif($program->approved_status == 1){
                        $approval = "Belum Diproses";
                    }
                    else{
                        $approval = "Telah Diluluskan";
                    }

                    $program->approval = $approval;

                    $program->address = $program->venue . ', ' . $program->postal_code . 
                    ', ' . $program->city . ', ' . $program->state;

                    $startDate = $program->start_date;
                    $program->start_date = DateController::parseDate($startDate);
                    $program->start = $program->start_date . ' ' . $program->start_time;

                    $endDate = $program->end_date;
                    $program->end_date = DateController::parseDate($endDate);
                    $program->end = $program->end_date . ' ' . $program->end_time;

                    $closeDate = $program->close_date;
                    $program->close_date = DateController::parseDate($closeDate);

                    $program->vol = 'Sukarelawan: ' . $program->vol_enrolled . '/' . $program->vol_limit . ' orang';
                    $program->poor = 'B40/OKU: ' . $program->poor_enrolled . '/' . $program->poor_limit . ' orang';

                    return $program;
                });

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
                ->join('program_specs as ps1', 'ps1.program_id', '=', 'programs.program_id')
                ->where('ps1.user_type_id', 2)
                ->join('program_specs as ps2', 'ps2.program_id', '=', 'programs.program_id')
                ->where('ps2.user_type_id', 3)
                ->leftJoin('users as processed', function($join) {
                    $join->on('processed.id', '=', 'programs.approved_by')
                         ->whereNotNull('programs.approved_by');
                })
                ->where([
                    ['u.status', 1],
                    ['u.id', $selectedUser],
                ]);

                // user select radio button other than Active
                if($selectedState != 3 && $selectedState != 4){
                    $query->where('programs.approved_status', $selectedState);
                }

                // user select option other than Semua Jenis
                if($selectedType == 'vol'){
                    $query->where('types.type_id', 1);
                }
                elseif($selectedType == 'skill'){
                    $query->where('types.type_id', 2);
                }
                elseif($selectedType != 'all'){
                    $query->where('programs.program_id', $selectedType);
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
                    'ps1.qty_limit as vol_limit',
                    'ps1.qty_enrolled as vol_enrolled',
                    'ps2.qty_limit as poor_limit',
                    'ps2.qty_enrolled as poor_enrolled',
                )
                ->orderBy('programs.updated_at', 'desc')
                ->get();



                // Transform the data but keep it as a collection of objects
                $selectedPrograms->transform(function ($program) {
                    $program->description = json_decode($program->description, true)['desc'] ?? '';
                    $program->address = $program->venue . ', ' . $program->postal_code . 
                    ', ' . $program->city . ', ' . $program->state;

                    $startDate = $program->start_date;
                    $program->start_date = DateController::parseDate($startDate);
                    $program->start = $program->start_date . ' ' . $program->start_time;

                    $endDate = $program->end_date;
                    $program->end_date = DateController::parseDate($endDate);
                    $program->end = $program->end_date . ' ' . $program->end_time;

                    $closeDate = $program->close_date;
                    $program->close_date = DateController::parseDate($closeDate);

                    if($program->approved_at != null){
                        $approved_at = explode(' ', $program->approved_at);
                        $program->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];
                    }

                    $program->vol = 'Sukarelawan: ' . $program->vol_enrolled . '/' . $program->vol_limit . ' orang';
                    $program->poor = 'B40/OKU: ' . $program->poor_enrolled . '/' . $program->poor_limit . ' orang';

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
}
