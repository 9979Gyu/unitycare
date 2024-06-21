<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Job;
use App\Models\Poor;
use App\Models\Job_Type;
use App\Models\Job_Offer;
use App\Models\Shift_Type;
use App\Models\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportApplication;
use App\Exports\ExportApplied;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyJoinEmail;

class ApplicationController extends Controller
{

    // Email to notify user about the creation of job
    public function notifyUser($applicationID){

        $apps = Application::where('applications.application_id', $applicationID)
        ->join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
        ->join('jobs', 'jobs.job_id', '=', 'jo.job_id')
        ->join('poors', 'poors.poor_id', '=', 'applications.poor_id')
        ->join('users', 'users.id', '=', 'poors.user_id')
        ->select(
            'jobs.position',
            'applications.approval_status',
            'applications.approved_at',
            'applications.applied_date',
            'users.username',
            'users.email',
        )
        ->first();
        
        if($apps->approved_at == null){
            $apps->approved_at = $apps->applied_date;
        }

        $date = explode(" ", $apps->approved_at);
        $convertedDate = DateController::parseDate($date[0]);
        
        Mail::to($apps->email)->send(new NotifyJoinEmail([
            'name' => $apps->username,
            'subject' => 'pekerjaan',
            'approval' => $apps->approval_status,
            'offer' => $apps->position,
            'datetime' => $convertedDate . ' ' . $date[1],
        ]));
    }

    // Function to display view applications
    public function index(){
        if(Auth::check()){

            $roleNo = Auth::user()->roleID;

            if($roleNo != 4){

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

                // Is B40 / OKU
                if($roleNo == 5){

                    $applications = Application::where('applications.status', 1)
                    ->join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
                    ->join('jobs as j', 'j.job_id', '=', 'jo.job_id')
                    ->join('poors as p', 'p.poor_id', '=', 'applications.poor_id')
                    ->where('p.user_id', Auth::user()->id)
                    ->select('j.name')
                    ->groupBy('j.name')
                    ->orderBy('j.name', 'asc')
                    ->get();

                    // View list of job offer which the application is made
                    return view('applications.view', compact('roleNo', 'applications', 'users'));
                }
                // Is admin or staff
                else if($roleNo == 1 || $roleNo == 2){
    
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

                    return view('applications.index', compact('roleNo', 'users'));

                }
                // Is enterprise
                else if($roleNo == 3){
                    return view('applications.index', compact('roleNo', 'users'));
                }

            }

            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    public function getApplicationsByCondition(Request $request){

        if(request()->ajax()){
            $userID = $request->get('selectedUser');
            $jobID = $request->get('selectedPosition');
            $state = $request->get('selectedState');
            $status = $request->get('status');
            $isSelected = $request->get('isSelected');

            // Handling for retrieve offers based on conditions
            if(isset($userID) && isset($jobID) && isset($state) && isset($status)){

                $query = Application::where('applications.status', 1)
                ->join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
                ->join('jobs as j', 'j.job_id', '=', 'jo.job_id')
                ->join('job_types as jt', 'jt.job_type_id', '=', 'jo.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', '=', 'jo.shift_type_id')
                ->join('poors as p', 'p.poor_id', '=', 'applications.poor_id')
                ->join('users as applier', 'applier.id', '=', 'p.user_id')
                ->join('users as u', 'u.id', '=', 'jo.user_id')
                ->leftJoin('users as processed', function($join) {
                    $join->on('processed.id', '=', 'applications.approved_by')
                         ->whereNotNull('applications.approved_by');
                });

                // If user choose Semua
                if($userID != "all"){
                    $query = $query->where('applier.id', $userID);
                }

                if($jobID != "all"){
                    $query = $query->where('jo.job_id', $jobID);
                }

                // If user choose to view by approval_status
                if($state != 3){
                    $query = $query->where('applications.approval_status', $state);
                }

                if($isSelected == 2){
                    $query = $query->where('applications.is_selected', 2);
                }

                $selectedOffers = $query->select(
                    'applications.*',
                    'applier.id as user_id',
                    'u.name as username', 
                    'u.contactNo as usercontact', 
                    'u.email as useremail',
                    'jo.venue',
                    'jo.state',
                    'jo.city', 
                    'jo.postal_code',
                    'jo.min_salary',
                    'jo.max_salary',
                    'jo.start_date',
                    'jo.start_time',
                    'jo.end_date',
                    'jo.end_time',
                    'processed.name as processedname', 
                    'processed.email as processedemail',
                    'j.name as jobname',
                    'j.position as jobposition',
                    'jt.name as typename',
                    'st.name as shiftname',
                    'applications.description->description as description',
                    'applications.description->reason as reason',
                )
                ->orderBy('applications.updated_at', 'desc')
                ->get();

                // Transform the data but keep it as a collection of objects
                $selectedOffers->transform(function ($offer) {

                    if($offer->approved_at != null){
                        $approved_at = explode(' ', $offer->approved_at);
                        $offer->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];
                    }

                    if($offer->approval_status == 0){
                        $approval = "Ditolak: " . $offer->reason;
                    }
                    elseif($offer->approval_status == 1){
                        $approval = "Belum Diproses";
                    }
                    else{
                        $approval = "Telah Diluluskan";
                    }

                    $offer->approval = $approval;

                    $offer->address = $offer->venue . ', ' . $offer->postal_code . 
                    ', ' . $offer->city . ', ' . $offer->state;

                    $startDate = $offer->start_date;
                    $endDate = $offer->end_date;

                    if($startDate != null && $endDate != null){
                        $offer->start_date = DateController::parseDate($startDate);
                        $offer->end_date = DateController::parseDate($endDate);

                        $offer->start = $offer->start_date . ' hingga ' . $offer->end_date;
                    }
                    else{
                        $offer->start = '';
                    }

                    $offer->end = $offer->start_time . ' hingga ' . $offer->end_time;

                    return $offer;
                });
            }

            if(isset($selectedOffers)){

                $table = Datatables::of($selectedOffers);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="justify-content-center">';
                    $btn .= '<a href="/joinoffer/' . $row->offer_id . '"><span class="btn btn-primary m-1"> Lihat </span></a>';
                    
                    if($row->user_id == Auth::user()->id){
                        if($row->approval_status == 2 && $row->is_selected == 1){
                            // Program is approved
                            $btn .= '<div>';
                            $btn .= '<a class="approveAnchor" href="#" id="' . $row->offer_id . '"><span class="btn btn-success m-1" data-bs-toggle="modal" data-bs-target="#approveModal"> Terima </span></a>';
                            $btn .= '<a class="declineAnchor" href="#" id="' . $row->offer_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';
                            $btn .= '</div>';
                        }
                        else{
                            $btn .= '<a class="deleteAnchor" href="#" id="' . $row->application_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                        }
                    }

                    $btn .= '</div>';

                    return $btn;

                });
    
                $table->rawColumns(['action']);

                return $table->make(true);
            }

        }

        return redirect('/');

    }

    public function create($id){

        if(Auth::check() && isset($id)){

            $offer = Job_Offer::where([
                ['offer_id', $id],
                ['status', 1],
            ])
            ->with(['organization', 'jobType', 'shiftType', 'job'])
            ->first();

            if($offer->start_date != null && $offer->end_date != null){
                $offer->start_date = DateController::parseDate($offer->start_date);
                $offer->end_date = DateController::parseDate($offer->end_date);
            }

            $alreadyApply = Job_Offer::where([
                ['status', 1],
                ['approval_status', 2],
                ['offer_id', $id],
            ])
            ->whereHas('applications', function($query){
                $query->where([
                    ['status', 1],
                    ['approval_status', '<>', 1],
                ])
                ->whereHas('poor', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            })
            ->count();

            $approval = Application::where([
                ['applications.offer_id', $id],
                ['applications.status', 1],
            ])
            ->join('poors as p', 'p.poor_id', '=', 'applications.poor_id')
            ->join('users as u', 'u.id', '=', 'p.user_id')
            ->where('u.id', Auth::user()->id)
            ->value('approval_status');

            $applicationExist = Application::where([
                ['status', 1],
                ['approval_status', 2],
                ['is_selected', 1],
            ])
            ->whereHas('jobOffer', function ($query) use ($id) {
                $query->where([
                    ['approval_status', 2],
                ]);
            })
            ->whereHas('poor', function ($query) {
                $query->where('user_id', Auth::user()->id);
            })
            ->count();

            // dd($applicationExist);
    
            return view('applications.add', compact('offer', 'applicationExist', 'alreadyApply', 'approval'));
        }

        return redirect('/login')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

    }

    public function store(Request $request){
    
        $rules= [
            "offerId" => "required",
            "reason" => "required"
        ];

        $validated = $request->validate($rules);

        if($validated){
            $offerID = $request->get("offerId");
            $description = $request->get('reason');
            $uid = Auth::user()->id;
            $roleNo = Auth::user()->roleID;
            
            // Get the current date
            $currentDateTime = date('Y-m-d H:i:s');

            $poorID = Poor::where([
                ['user_id', $uid],
                ['status', 1]
            ])
            ->value("poor_id");
    
            if(isset($description)){
                $desc = [
                    "description" => $description,
                    "reason" => "",
                ];
            }
    
            if(isset($offerID) && isset($poorID)){

                $application = new Application([
                    'applied_date' => $currentDateTime,
                    'offer_id' => $offerID,
                    'poor_id' => $poorID,
                    'status' => 1,
                    'approval_status' => 1,
                    'description' => json_encode($desc),
                    'is_selected' => 1,
                ]);
    
                $result = $application->save();
    
                if($result){
                    $this->notifyUser($application->application_id);
                    return redirect('/viewoffer')->with('success', 'Data berjaya disimpan');
                }
            }
            
        }

        return redirect()->back()->withErrors(["message" => "Data tidak berjaya disimpan"]);
        
    }

    public function destroy(Request $request){
        $appID = $request->get('applicationID');

        $update = Application::where('application_id', $appID)
        ->update([
            'status' => 0
        ]);

        if($update){
            return redirect('/viewapplication')->with('success', 'Data berjaya dipadam');
        }
        else{
            return redirect('/viewapplication')->withErrors(['message' => "Data tidak berjaya dipadam"]);
        }

    }

    public function dismiss(Request $request){
        //
        $id =  $request->get('offerID');

        if(isset($id)){
            $result = Application::where([
                ['status', 1],
                ['offer_id', $id],
            ])
            ->update([
                'status' => 0,
                'updated_at' => date('Y-m-d H:i:s')
            ]);
    
            if($result){
                return redirect('/viewoffer')->with(["success" => "Data berjaya dipadam"]);
            }
    
        }

        return redirect('/viewoffer')->withErrors(["message" => "Tidak berjaya dipadam"]);
    }

    public function retrieveApplication($state, $userID, $jobID, $startDate, $endDate){
        if($state == 4){
            $status = 0;
        }
        else{
            $status = 1;
        }

        $query = Application::where('applications.status', $status)
        ->join('job_offers as jo', 'jo.offer_id', 'applications.offer_id')
        ->join('poors', 'poors.poor_id', '=', 'applications.poor_id')
        ->join('users as u', 'u.id', '=', 'poors.user_id')
        ->join('jobs as j', 'j.job_id', '=', 'jo.job_id')
        ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
        ->join('education_levels as el', 'el.edu_level_id', '=', 'poors.education_level')
        ->leftJoin('users as processed', function($join) {
            $join->on('processed.id', '=', 'applications.approved_by')
                 ->whereNotNull('applications.approved_by');
        });

        if($startDate != null && $endDate != null){

            $query->where([
                ['applications.applied_date', '>=', $startDate],
                ['applications.applied_date', '<=', $endDate],
            ]);
        }

        if($userID != "all"){
            $query = $query->where('jo.user_id', $userID);
        }

        if($state == 1 || $state == 0 || $state == 2){
            $query = $query->where('applications.approval_status', $state);
        }

        if($jobID != "all"){
            $query = $query->where('j.job_id', $jobID);
        }

        if($state == "is_selected"){
            $query = $query->where('applications.is_selected', 2);
        }

        $selectedApplication = $query->select(
                'applications.*',
                'applications.description->description as description',
                'applications.description->reason as reason',
                'u.name as username',
                'u.email as useremail',
                'u.contactNo as usercontact',
                'u.address',
                'u.state',
                'u.city',
                'u.postalCode',
                'processed.name as processedname', 
                'processed.email as processedemail',
                'dt.name as category',
                'el.name as edu_level',
                'j.position as position'
            )
            ->orderBy("applications.applied_date", "asc")
            ->get();

        // Transform the data but keep it as a collection of objects
        $selectedApplication->transform(function ($item) {

            if($item->approved_at != null){
                $approved_at = explode(' ', $item->approved_at);
                $item->approved_at = DateController::parseDate($approved_at[0]) . ' ' . DateController::formatTime($approved_at[1]);
            }

            if($item->approval_status == 0){
                $approval = "Ditolak: " . $item->reason;
            }
            elseif($item->approval_status == 1){
                $approval = "Belum Diproses";
            }
            else{
                $approval = "Telah Diluluskan";
            }

            $item->approval = $approval;

            $item->address = $item->address . ', ' . $item->postalCode . 
            ', ' . $item->city . ', ' . $item->state;

            $appliedDate = explode(' ', $item->applied_date);
            $item->applied_date = DateController::parseDate($appliedDate[0]) . ' ' . DateController::formatTime($appliedDate[1]);

            return $item;
        });

        return $selectedApplication;
    }

    // Function to get list of application
    public function getApplicationsDatatable(Request $request){
        if(request()->ajax()){

            $state = $request->get("state");
            $userID = $request->get("userID");
            $jobID = $request->get("jobID");
            $startDate = $request->get("startDate");
            $endDate = $request->get("endDate");

            if(isset($jobID)){
                $selectedApplication = $this->retrieveApplication($state, $userID, $jobID, $startDate, $endDate);
            }

            if ($selectedApplication === null || $selectedApplication->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'draw' => $request->input('draw', 1), // Ensure to return the draw number
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                ]);
            }

            $table = Datatables::of($selectedApplication);

            $table->addColumn('action', function ($row) {
                $token = csrf_token();
                $btn = '<div class="justify-content-center">';
                $btn .= '<a href="/joinoffer/' . $row->offer_id . '"><span class="btn btn-primary m-1"> Lihat </span></a>';

                if(Auth::user()->roleID == 3){
                    if($row->approval_status == 1){
                        // pending approval
                        $btn .= '<div>';
                        $btn .= '<a class="approveAnchor" href="#" id="' . $row->application_id . '"><span class="btn btn-success m-1" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a>';
                        $btn .= '<a class="declineAnchor" href="#" id="' . $row->application_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';
                        $btn .= '</div>';
                    }
                }
                $btn .= '</div>';
            
                return $btn;
            });

            $table->rawColumns(['action']);
            return $table->make(true);

        }

        return redirect('/');

    }

    public function updateApproval(Request $request){

        // application id
        $id = $request->get("offerID");
        $status = $request->get("approval_status");

        if(isset($id)){
            $update = 0;

            if($status == 2){

                // Update the program details
                $update = Application::where([
                    ['application_id', $id],
                    ['status', 1],
                ])
                ->update([
                    'approval_status' => 2,
                    'approved_by' => Auth::user()->id, 
                    'approved_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
            }
            else{
                // Get the current description
                $currentDesc = Application::where('application_id', $id)
                ->value('description');

                // Decode the JSON to an associative array
                $descArray = json_decode($currentDesc, true);

                // Update the 'reason' field
                $descArray['reason'] = $request->get('reason');

                // Encode the array back to JSON
                $newDesc = json_encode($descArray);

                // Update the program details
                $update = Application::where([
                    ['application_id', $id],
                    ['status', 1],
                ])
                ->update([
                    'approval_status' => 0,
                    'approved_by' => Auth::user()->id, 
                    'description' => $newDesc,
                    'approved_at' => date('Y-m-d H:i:s'),
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);
                    
            }

            if($update){
                $this->notifyUser($id);
            }
            
            // Direct user to view page with success messasge
            return redirect('/viewapplication')->with('success', 'Data berjaya dikemaskini');
        }

        // direct user to view page with error messasge
        return redirect('/viewapplication')->withErrors(['message' => "Data tidak berjaya dikemaskini"]);

    }

    // Function to update user confirm for job offer
    public function confirmOffer(Request $request){

        $id = $request->get('offerID');
        $status = $request->get('approval_status');

        $apps = Application::where([
            ['offer_id', $id],
            ['approval_status', 2],
        ]);

        $update = $apps->update([
            'is_selected' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);

        // If successfully update the status, decline other offer
        if($update){

            $poorID = Poor::where('user_id', Auth::user()->id)->value('poor_id');
            // Update employment status
            $employed = Poor::where('user_id', Auth::user()->id)->update(['employment_status' => 1]);

            if($employed){

                $appsid = $apps->value('application_id');

                $currentDesc = Application::where('application_id', $appsid)
                ->value('description');

                // Decode the JSON to an associative array
                $descArray = json_decode($currentDesc, true);

                // Update the 'reason' field
                $descArray['reason'] = "System tolak secara automatik";

                // Encode the array back to JSON
                $newDesc = json_encode($descArray);

                // Update other applications status to 0 if the approval status is 1
                $updateOthers = Application::where([
                    ['status', 1],
                    ['approval_status', '>=', 1],
                    ['poor_id', $poorID],
                    ['application_id', '<>', $appsid]
                ])
                ->update([
                    'status' => 0,
                    'is_selected' => 0,
                    'description' => $newDesc
                ]);

                if($updateOthers){

                    // Update the number of quantity enrolled in job_offers
                    $updateQty = Job_Offer::where('offer_id', $id)
                    ->increment('quantity_enrolled');

                    $jobOffer = Job_Offer::where('offer_id', $id)->first();

                    if ($jobOffer->quantity_enrolled === $jobOffer->quantity) {
                        // If quantity_enrolled is equal to quantity, set is_full to 1
                        $jobOffer->update(['is_full' => 1]);

                        // Update remaining user application
                        Application::where([
                            ['offer_id', $id],
                            ['status', 1],
                            ['approval_status', 1],
                        ])
                        ->update([
                            'approval_status' => 0,
                            'description' => $newDesc,
                        ]);
                        
                    }

                    // Send email to notify organization

                    // Direct user to view page with success messasge
                    return redirect('/viewapplication')->with('success', 'Data berjaya dikemaskini');
                }
                
            }
        }

        // direct user to view page with error messasge
        return redirect('/viewapplication')->withErrors(['message' => "Data tidak berjaya dikemaskini"]);
    }

    // Function to export offer info
    public function exportApplications(Request $request){
        
        // Validate the request data
        $rules = [
            'statusFilter' => 'required',
            'position' => 'required',
            'organization' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $state = $request->get('statusFilter');
            $jobID = $request->get("position");
            $userID = $request->get('organization');
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if(isset($jobID)){
                $selectedApplication = $this->retrieveApplication($state, $userID, $jobID, $startDate, $endDate);
            }

            return Excel::download(new ExportApplication($selectedApplication), 
                'Senarai Permohonan Kerja - ' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

    public function exportApplied(Request $request){

        // Validate the request data
        $rules = [
            'organization' => 'required',
            'position' => 'required',
            'statusFilter' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){

            $userID = $request->get('organization');
            $jobID = $request->get('position');
            $state = $request->get('statusFilter');
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            // Handling for retrieve offers based on conditions
            if(isset($userID) && isset($jobID) && isset($state)){

                if($state == "is_selected"){
                    $isSelected = 2;
                    $state = 3;
                }
                
                $query = Application::where([
                    ['applications.status', 1],
                    ['applications.applied_date', '>=', $startDate],
                    ['applications.applied_date', '<=', $endDate],
                ])
                ->join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
                ->join('jobs as j', 'j.job_id', '=', 'jo.job_id')
                ->join('job_types as jt', 'jt.job_type_id', '=', 'jo.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', '=', 'jo.shift_type_id')
                ->join('poors as p', 'p.poor_id', '=', 'applications.poor_id')
                ->join('users as applier', 'applier.id', '=', 'p.user_id')
                ->join('users as u', 'u.id', '=', 'jo.user_id')
                ->leftJoin('users as processed', function($join) {
                    $join->on('processed.id', '=', 'applications.approved_by')
                            ->whereNotNull('applications.approved_by');
                });

                // If user choose Semua
                if($userID != "all"){
                    $query = $query->where('applier.id', $userID);
                }

                if($jobID != "all"){
                    $query = $query->where('jo.job_id', $jobID);
                }

                // If user choose to view by approval_status
                if($state != 3 && $state != 4){
                    $query = $query->where('applications.approval_status', $state);
                }

                if($isSelected == 2){
                    $query = $query->where('applications.is_selected', 2);
                }

                $selectedApplications = $query->select(
                    'applications.*',
                    'u.name as username', 
                    'u.contactNo as usercontact', 
                    'u.email as useremail',
                    'jo.venue',
                    'jo.state',
                    'jo.city', 
                    'jo.postal_code',
                    'jo.min_salary',
                    'jo.max_salary',
                    'jo.start_date',
                    'jo.start_time',
                    'jo.end_date',
                    'jo.end_time',
                    'processed.name as processedname', 
                    'processed.email as processedemail',
                    'j.name as jobname',
                    'j.position as jobposition',
                    'jt.name as typename',
                    'st.name as shiftname',
                    'applications.description->description as description',
                    'applications.description->reason as reason',
                )
                ->orderBy('applications.updated_at', 'desc')
                ->get();

                // Transform the data but keep it as a collection of objects
                $selectedApplications->transform(function ($offer) {

                    if($offer->approved_at != null){
                        $approved_at = explode(' ', $offer->approved_at);
                        $offer->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];
                    }

                    if($offer->approval_status == 0){
                        $approval = "Ditolak: " . $offer->reason;
                    }
                    elseif($offer->approval_status == 1){
                        $approval = "Belum Diproses";
                    }
                    else{
                        $approval = "Telah Diluluskan";
                    }

                    $offer->approval = $approval;

                    $offer->address = $offer->venue . ', ' . $offer->postal_code . 
                    ', ' . $offer->city . ', ' . $offer->state;

                    $startDate = $offer->start_date;
                    $endDate = $offer->end_date;

                    if($startDate != null && $endDate != null){
                        $offer->start_date = DateController::parseDate($startDate);
                        $offer->end_date = DateController::parseDate($endDate);

                        $offer->start = $offer->start_date . ' hingga ' . $offer->end_date;
                    }
                    else{
                        $offer->start = '';
                    }

                    $offer->end = $offer->start_time . ' hingga ' . $offer->end_time;

                    return $offer;
                });
            }

            if(isset($selectedApplications)){
                return Excel::download(new ExportApplied($selectedApplications), 
                    'Senarai Permohonan Kerja - ' . time() . '.xlsx'
                );
            }

        }
        
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
    }

    // function to display num of application made for each offer in bar chart
    public function appBarChart(Request $request){
        
        $state = $request->get("selectedState");
        $userID = $request->get("selectedUser");
        $jobID = $request->get("selectedPosition");
        $startDate = $request->get("startDate");
        $endDate = $request->get("endDate");

        if(isset($jobID)){

            if($state == 4){
                $status = 0;
            }
            else{
                $status = 1;
            }

            $query = Application::where('applications.status', $status)
            ->join('job_offers as jo', 'jo.offer_id', 'applications.offer_id')
            ->join('jobs as j', 'j.job_id', '=', 'jo.job_id');

            if($startDate != null && $endDate != null){
    
                $query->where([
                    ['applications.applied_date', '>=', $startDate],
                    ['applications.applied_date', '<=', $endDate],
                ]);
            }

            if($userID != "all"){
                $query = $query->where('jo.user_id', $userID);
            }

            if($state == 1 || $state == 0 || $state == 2){
                $query = $query->where('applications.approval_status', $state);
            }

            if($jobID != "all"){
                $query = $query->where('j.job_id', $jobID);
            }

            if($state == "is_selected"){
                $query = $query->where('applications.is_selected', 2);
            }

            $num = $query->groupBy('j.position')
            ->selectRaw('j.position as labels, COUNT(*) as data')
            ->get();

            return response()->json([
                'labels' => $num->pluck('labels'),
                'data' => $num->pluck('data'),
            ]);

        }
    }

}
