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
    public function notifyUser($offerID){

        $offer = Job_Offer::where('job_offers.offer_id', $offerID)
        ->join('jobs', 'jobs.job_id', '=', 'job_offers.job_id')
        ->join('applications as a', 'a.offer_id', '=', 'job_offers.offer_id')
        ->select(
            'jobs.position',
            'a.approval_status',
            'a.approved_at',
            'a.applied_date',
            'a.poor_id'
        )
        ->first();

        $user = User::join('poors', 'poors.user_id', '=', 'users.id')
        ->where('poors.poor_id', $offer->poor_id)
        ->select('users.username', 'users.email')
        ->first();
        
        if($offer->approved_at == null){
            $offer->approved_at = $offer->applied_date;
        }

        $date = explode(" ", $offer->approved_at);
        $convertedDate = DateController::parseDate($date[0]);

        Mail::to($user->email)->send(new NotifyJoinEmail([
            'name' => $user->username,
            'subject' => 'pekerjaan',
            'approval' => $offer->approval_status,
            'offer' => $offer->position,
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
                    return view('applications.view', compact('roleNo', 'users', 'applications'));
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

                $selectedOffers = $query->select(
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
                    
                    //  Is admin or staff
                    if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                        
                        if($row->approval_status == 1){
                            // Program is pending approval
                            $btn .= '<div>';
                            $btn .= '<a class="approveAnchor" href="#" id="' . $row->offer_id . '"><span class="btn btn-success m-1" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a>';
                            $btn .= '<a class="declineAnchor" href="#" id="' . $row->offer_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';
                            $btn .= '</div>';
                        }
                    }
                    else{
                        if($row->user_id == Auth::user()->id){
                            if($row->approval_status == 1){
                                
                                // Program is pending approval
                                $btn .= '<div>';
                                $btn .= '<a href="/editoffer/' . $row->offer_id . '"><span class="btn btn-warning m-1"> Kemaskini </span></a>';
                                $btn .= '</div>';
                            }
                        }
                        $btn .= '<a class="deleteAnchor" href="#" id="' . $row->offer_id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
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

            $offer->start_date = DateController::parseDate($offer->start_date);
            $offer->end_date = DateController::parseDate($offer->end_date);

            $alreadyApply = Job_Offer::where([
                ['status', 1],
                ['approval_status', 2],
                ['offer_id', $id],
            ])
            ->whereHas('applications', function($query){
                $query->where([
                    ['status', 1],
                    ['approval_status', '>=', 1],
                ])
                ->whereHas('poor', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            })
            ->count();

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

            $selectedApplication = Application::where([
                ['status', 1],
                ['approval_status', 2],
                ['is_selected', 2],
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
    
            return view('applications.add', compact('offer', 'applicationExist', 'alreadyApply', 'selectedApplication'));
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
                    $this->notifyUser($offerID);
                    return redirect('/viewoffer')->with('success', 'Data berjaya disimpan');
                }
            }
            
        }

        return redirect()->back()->withErrors(["message" => "Data tidak berjaya disimpan"]);
        
    }

    public function dismiss(Request $request)
    {
        //
        $id =  $request->selectedID;

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

    // Function to get list of application
    public function getApplicationsDatatable(Request $request)
    {
        if(request()->ajax()){

            $selectedState = $request->get("selectedState");
            $userID = $request->get("userID");
            $status = $request->get("status");
            $selectedPosition = $request->get("positionID");

            if(isset($selectedPosition)){

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

                if($userID != "all"){
                    $query = $query->where('jo.user_id', $userID);
                }

                if($selectedState != 3){
                    $query = $query->where('applications.approval_status', $selectedState);
                }

                if($selectedPosition != "all"){
                    $query = $query->where('j.job_id', $selectedPosition);
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
                        'poors.disability_type',
                        'dt.name as category',
                        'el.name as edu_level',
                        'j.position as position'
                    )
                    ->orderBy("applications.applied_date", "asc")
                    ->get();

                // Transform the data but keep it as a collection of objects
                $selectedApplication->transform(function ($item) {

                    if($item->approved_at == null){
                        $item->approved_at = $item->updated_at;
                    }

                    $approved_at = explode(' ', $item->approved_at);
                    $item->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];

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
                    $item->applied_date = DateController::parseDate($appliedDate[0]) . ' ' . $appliedDate[1];

                    return $item;
                });
            }

            if(isset($selectedApplication)){

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

        }

        return redirect('/');

    }

    public function updateApproval(Request $request){
        $id = $request->get("selectedID");

        $poorID = Application::where([
            ['application_id', $id],
            ['status', 1],
        ])
        ->value('poor_id');
        
        // Get the current date and time
        $currentDateTime = date('Y-m-d H:i:s');

        if(isset($id) && isset($currentDateTime)){
            // Update the program details
            $update = Application::where([
                ['application_id', $id],
                ['status', 1],
            ])
            ->update([
                'approval_status' => 2,
                'approved_by' => Auth::user()->id, 
                'approved_at' => $currentDateTime,
            ]);

            if($update){
                
                $offerID = Application::where('application_id', $id)->value('offer_id');

                // Update the number of quantity enrolled in job_offers
                $updateQty = Job_Offer::where('offer_id', $offerID)
                ->increment('quantity_enrolled')
                ->first();
            
                $this->notifyUser($offerID);
            }
            
            // Direct user to view program page with success messasge
            return redirect('/viewapplication')->with('success', 'Data berjaya dikemaskini');
        }

        // direct user to view program page with error messasge
        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);

    }

    public function declineApproval(Request $request){

        // Get the current date and time
        $currentDateTime = date('Y-m-d H:i:s');

        $id = $request->get("selectedID");

        // Get the current description
        $currentDesc = Application::where('application_id', $id)
        ->value('description');

        // Decode the JSON to an associative array
        $descArray = json_decode($currentDesc, true);

        // Update the 'reason' field
        $descArray['reason'] = $request->get('reason');

        // Encode the array back to JSON
        $newDesc = json_encode($descArray);

        if(isset($id)){
            // Update the program details
            $update = Application::where([
                ['application_id', $id],
                ['status', 1],
            ])
            ->update([
                'approval_status' => 0,
                'approved_by' => Auth::user()->id, 
                'description' => $newDesc,
                'approved_at' => $currentDateTime,
            ]);

            // If successfully update the program
            if($update){

                $offerID = Application::where('application_id', $id)->value('offer_id');
                $this->notifyUser($offerID);

                // direct user to view program page with success messasge
                return redirect('/viewapplication')->with('success', 'Data berjaya dikemaskini');
            }
        }

        // direct user to view program page with error messasge
        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);

    }

    // Function to update user confirm for job offer
    public function confirmOffer(Request $request){

        $id = $request->get('selectedID');

        // Update user selection
        $update = Application::where([
            ['application_id', $id],
            ['approval_status', 2],
            ['status', 1]
        ])
        ->update([
            ['is_selected' => 2],
        ]);

        // If successfully update the status, decline other offer
        if($update){

            // Update employment status
            $update = Poor::where('poor_id', $poorID)->update(['employment_status' => 1]);

            if($update){
                
                // Update other applications status to 0 if the employment status is 1
                $updateOthers = Application::where([
                    ['status', 1],
                    ['approval_status', 1],
                    ['poor_id', $poorID],
                    ['application_id', '<>', $id]
                ])
                ->update([
                    ['status' => 0],
                    ['is_selected' => 0]
                ]);

                if($updateOthers){

                    // Send email to notify organization

                    // Direct user to view program page with success messasge
                    return redirect('/viewapplication')->with('success', 'Data berjaya dikemaskini');
                }
                
            }
        }

        // direct user to view program page with error messasge
        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);
    }

    // Function to update user reject for job offer
    public function rejectOffer(Request $request){

        $id = $request->get('selectedID');

        // Update user selection
        $update = Application::where([
            ['application_id', $id],
            ['approval_status', 2],
            ['status', 1]
        ])
        ->update([
            ['is_selected' => 0],
        ]);

        // If successfully update the status, decline other offer
        if($update){

            // Send email to notify organization

            // Direct user to view program page with success messasge
            return redirect('/viewapplication')->with('success', 'Data berjaya dikemaskini');
        }

        // direct user to view program page with error messasge
        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);
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
            $status = 1;
            $selectedPosition = $request->get("position");
            $userID = $request->get('organization');
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if($state == 4){
                $status = 0;
            }

            if(isset($selectedPosition)){

                $query = Application::where([
                    ['applications.status', $status],
                    ['applied_date', '>=', $startDate],
                    ['applied_date', '<=', $endDate],
                ])
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

                if($userID != "all"){
                    $query = $query->where('jo.user_id', $userID);
                }

                if($state != 3 && state != 4){
                    $query = $query->where('applications.approval_status', $state);
                }

                if($selectedPosition != "all"){
                    $query = $query->where('j.job_id', $selectedPosition);
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
                        'poors.disability_type',
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
                        $item->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];
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
                    $item->applied_date = DateController::parseDate($appliedDate[0]) . ' ' . $appliedDate[1];

                    return $item;
                });
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
}
