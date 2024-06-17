<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\Job;
use App\Models\Job_Offer;
use App\Models\Job_Type;
use App\Models\Shift_Type;
use App\Models\User;
use DataTables;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

use App\Exports\ExportOffer;
use Maatwebsite\Excel\Facades\Excel;

use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyJoinEmail;

class OfferController extends Controller
{
    // Function to display the view for list of offer
    public function index(){

        // check if already log in
        if(Auth::check()){

            // Get login user role
            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            // if is B40 / OKU
            if($roleID == 5){

                // Display view offer in card container
                return view('offers.view', compact('userID', 'roleID'));

            }
            // is admin, staff or enterprise
            else if($roleID < 4){

                if($roleID == 3){
                    $users = User::where('users.id', $userID)
                    ->get();
                }
                else{

                    // Get the list of user created offer
                    $query = User::where('users.status', 1)
                    ->join('job_offers as jo', 'jo.user_id', 'users.id')
                    ->where('jo.status', 1);

                    $users = $query
                    ->get();
                }

                // Get unique only
                $users = $users->unique()->all();

                // Display view offer in datatable
                return view('reports.offers.index', compact('users', 'roleID'));
            }

            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

        }

        // if all false
        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    // Function to return list of jobs based on user id
    public function getJobsByUser(Request $request){
        $userID = $request->get('selectedUser');

        $jobs = Job_Offer::join('jobs', 'jobs.job_id', '=', 'job_offers.job_id')
        ->where([
            ['jobs.status', 1],
            ['job_offers.status', 1],
        ]);

        if($userID != "all"){
            $jobs = $jobs->where('job_offers.user_id', $userID);
        }

        $jobs = $jobs->select(
            'jobs.name', 
        )
        ->groupBy('jobs.name')
        ->orderBy('jobs.name')
        ->get();

        // $jobs = $jobs->unique('name');

        return response()->json($jobs);
    }

    // Function to return all list of jobs
    public function getAllJobs(){

        $jobs = Job::where('status', 1)
            ->select('name')
            ->groupBy('name')
            ->orderBy('name')
            ->get();

        return response()->json(['jobs' => $jobs]);
    }

    // Function to get list of position related to the selected jobs from database
    public function getPositions(Request $request){
        $jobName = $request->get('jobName');
        $userID = $request->get('userID');

        $positions = Job::where('jobs.status', 1)
        ->join('job_offers', 'job_offers.job_id', '=', 'jobs.job_id')
        ->where('job_offers.status', 1);

        if($jobName != "all"){
            $positions = $positions->where('jobs.name', 'LIKE', '%' . $jobName . '%');
        }

        if($userID != "all"){
            $positions = $positions->where('job_offers.user_id', $userID);
        }

        $positions = $positions->get();

        $positions = $positions->unique('position');

        return response()->json($positions);
    }

    // Function to get all list of position related to the selected jobs from database
    public function getAllPositions(Request $request){
        $jobName = $request->get('jobName');

        $positions = Job::where('jobs.status', 1)
        ->where('jobs.name', 'LIKE', '%' . $jobName . '%')
        ->get();

        $positions = $positions->unique('job_id');

        return response()->json($positions);
    }

    public function getOffersByPositionDatatable(Request $request){
        if(request()->ajax()){
            $userID = $request->get('selectedUser');
            $jobID = $request->get('selectedPosition');
            $state = $request->get('selectedState');
            $status = $request->get('status');

            // Handling for retrieve offers based on conditions
            if(isset($userID) && isset($jobID) && isset($state) && isset($status)){

                $query = Job_Offer::where('job_offers.status', $status)
                ->join('users as u', 'u.id', '=', 'job_offers.user_id')
                ->leftJoin('users as processed', function($join) {
                    $join->on('processed.id', '=', 'job_offers.approved_by')
                         ->whereNotNull('job_offers.approved_by');
                })
                ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
                ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id');

                // If user choose Semua
                if($userID != "all"){
                    $query->where('job_offers.user_id', $userID);
                }

                if($jobID != "all"){
                    $query->where('job_offers.job_id', $jobID);
                }

                // If user choose to view by approval_status
                if($state != 3){
                    $query = $query->where('job_offers.approval_status', $state);
                }

                $selectedOffers = $query->select(
                    'job_offers.*',
                    'u.name as username', 
                    'u.contactNo as usercontact', 
                    'u.email as useremail',
                    'processed.name as processedname', 
                    'processed.email as processedemail',
                    'j.name as jobname',
                    'j.position as jobposition',
                    'jt.name as typename',
                    'st.name as shiftname',
                    'job_offers.description->description as description',
                    'job_offers.description->reason as reason',
                )
                ->orderBy('job_offers.updated_at', 'desc')
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
                   
                    $offer->people = $offer->quantity_enrolled . '/' . $offer->quantity . ' orang';

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

    // Function to display create offer view
    public function create(){
        // check if already log in
        if(Auth::check()){
            // Get login user role
            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            if($roleID == 1 || $roleID == 3){

                $jobTypes = $this->getJobTypes();
                $shiftTypes = $this->getShiftTypes();

                // Display view offer in datatable
                return view('offers.add', compact('jobTypes', 'shiftTypes'));
            }

            // if all false
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

        }

        // if all false
        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);
    }

    // Function to store the offer detail
    public function store(Request $request){
        $jobTypeID = $request->get('jobType');

        // Is not sepenuh masa job
        if($jobTypeID != 1){
            $rules = [
                'position' => 'required',
                'jobType' => 'required',
                'shiftType' => 'required',
                'address' => 'required',
                'postalCode' => 'required',
                'state' => 'required',
                'city' => 'required',
                'description' => 'required',
                'salaryStart' => 'required',
                'salaryEnd' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
                'quantity' => 'required',
            ];
        }
        else{
            $rules = [
                'position' => 'required',
                'jobType' => 'required',
                'shiftType' => 'required',
                'postalCode' => 'required',
                'address' => 'required',
                'state' => 'required',
                'city' => 'required',
                'description' => 'required',
                'salaryStart' => 'required',
                'salaryEnd' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
                'quantity' => 'required',
            ];
        }

        $validated = $request->validate($rules);

        if($validated){

            $desc = [
                "description" => $request->get('description'),
                "reason" => "",
            ];

            $job_offer = new job_offer([
                'job_id' => $request->get('position'),
                'job_type_id' => $request->get('jobType'),
                'shift_type_id' => $request->get('shiftType'),
                'venue' => ucwords(trim($request->get('address'))),
                'postal_code' => $request->get('postalCode'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'description' => json_encode($desc),
                'status' => 1,
                'min_salary' => $request->get('salaryStart'),
                'max_salary' => $request->get('salaryEnd'),
                'user_id' => Auth::user()->id,
                'approval_status' => 1,
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'quantity' => $request->get('quantity'),
                'quantity_enrolled' => 0,
            ]);

            $result = $job_offer->save();

            if($result){
                $this->notifyUser($job_offer->offer_id);

                return redirect('/viewoffer')->with('success', 'Data berjaya disimpan');
            }
        }

        return redirect('/viewoffer')->withErrors(['message' => 'Kemaskini data tidak berjaya']);

    }

    // Function to display edit offer view
    public function edit($id){
        // check if already log in
        if(Auth::check()){
            // Get login user role
            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            if($roleID == 1 || $roleID == 3){
                // Get the list of user created offer
                $query = Job_Offer::where('job_offers.offer_id', $id)
                ->join('users as u', 'u.id', 'job_offers.user_id')
                ->join('jobs as j', 'j.job_id', 'job_offers.job_id')
                ->join('job_types as jt', 'jt.job_type_id', 'job_offers.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', 'job_offers.shift_type_id')
                ->where([
                    ['job_offers.status', 1],
                    ['j.status', 1],
                    ['jt.status', 1],
                    ['st.status', 1],
                    ['u.status', 1],
                ]);

                // Is enterprise
                if($roleID == 3){
                    $query = $query->where('u.id', $userID);
                }

                $offer = $query->select(
                    'j.name as jobname',
                    'j.position as jobposition',
                    'jt.job_type_id',
                    'jt.name as typename',
                    'st.shift_type_id',
                    'st.name as shiftname',
                    'job_offers.*',
                    'job_offers.description->description as description',
                )
                ->first();

                $jobTypes = $this->getJobTypes();
                $shiftTypes = $this->getShiftTypes();

                // Display view offer in datatable
                return view('offers.edit', compact('offer', 'jobTypes', 'shiftTypes'));
            }

            // if all false
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

        }

        // if all false
        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);
    }

    // Function to get all job types
    function getJobTypes(){
        $jobTypes = Job_Type::where('status', 1)
        ->get();

        return $jobTypes;
    }

    // Function to get all shift types
    function getShiftTypes(){
        $shiftTypes = Shift_Type::where('status', 1)
        ->get();

        return $shiftTypes;

    }

    // Function to update the offer detail
    public function update(Request $request){
        $jobTypeID = $request->get('jobType');

        // Is not sepenuh masa job
        if($jobTypeID != 1){
            $rules = [
                'position' => 'required',
                'jobType' => 'required',
                'shiftType' => 'required',
                'address' => 'required',
                'postalCode' => 'required',
                'state' => 'required',
                'city' => 'required',
                'description' => 'required',
                'salaryStart' => 'required',
                'salaryEnd' => 'required',
                'start_date' => 'required',
                'end_date' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
                'quantity' => 'required',
            ];
        }
        else{
            $rules = [
                'position' => 'required',
                'jobType' => 'required',
                'shiftType' => 'required',
                'postalCode' => 'required',
                'address' => 'required',
                'state' => 'required',
                'city' => 'required',
                'description' => 'required',
                'salaryStart' => 'required',
                'salaryEnd' => 'required',
                'start_time' => 'required',
                'end_time' => 'required',
                'quantity' => 'required',
            ];
        }

        $validated = $request->validate($rules);

        if($validated){

            $desc = [
                "description" => $request->get('description'),
                "reason" => "",
            ];

            $id = $request->get('offerID');

            $result = Job_Offer::where('offer_id', $id)
            ->update([
                'job_id' => $request->get('position'),
                'job_type_id' => $request->get('jobType'),
                'shift_type_id' => $request->get('shiftType'),
                'venue' => ucwords(trim($request->get('address'))),
                'postal_code' => $request->get('postalCode'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'description' => json_encode($desc),
                'status' => 1,
                'min_salary' => $request->get('salaryStart'),
                'max_salary' => $request->get('salaryEnd'),
                'user_id' => Auth::user()->id,
                'approval_status' => 1,
                'approved_by' => null,
                'approved_at' => null,
                'start_date' => $request->get('start_date'),
                'start_time' => $request->get('start_time'),
                'end_date' => $request->get('end_date'),
                'end_time' => $request->get('end_time'),
                'quantity' => $request->get('quantity'),
                'quantity_enrolled' => 0,
                'updated_at' => date('Y-m-d H:i:s'),
            ]);

            if($result){
                $this->notifyUser($id);

                return redirect('/viewoffer')->with('success', 'Data berjaya dikemaskini');
            }
        }

        return redirect('/viewoffer')->withErrors(['message' => 'Kemaskini data tidak berjaya']);

    }

    // Function to update approval of offer
    public function updateApproval(Request $request){

        if(Auth::check()){

            // get the approval status
            $approval = $request->get('approval_status');
            $offerID = $request->get('offerID');
            
            $roleID = Auth::user()->roleID;
            $userID = Auth::user()->id;

            if($roleID == 1 || $roleID == 2 || $roleID == 3){

                // Get the current description
                $currentDesc = Job_Offer::where('offer_id', $offerID)
                ->value('description');

                // Decode the JSON to an associative array
                $descArray = json_decode($currentDesc, true);

                // Update the 'reason' field
                $descArray['reason'] = $request->get('reason');

                // Encode the array back to JSON
                $newDesc = json_encode($descArray);
                $result = 0;

                // Approve offer
                if($approval == 2){

                    $result = Job_Offer::where([
                        ['job_offers.offer_id', $offerID],
                        ['job_offers.status', 1],
                    ])
                    ->update([
                        'approval_status' => 2,
                        'approved_by' => $userID,
                        'approved_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                }
                // Decline offer
                else{

                    $result = Job_Offer::where([
                        ['job_offers.offer_id', $offerID],
                        ['job_offers.status', 1],
                    ])
                    ->update([
                        'description' => $newDesc,
                        'approval_status' => 0,
                        'approved_by' => $userID,
                        'approved_at' => date('Y-m-d H:i:s'),
                        'updated_at' => date('Y-m-d H:i:s'),
                    ]);

                }

                if($result){

                    $this->notifyUser($offerID);

                    return redirect('/viewoffer')->with('success', 'Data berjaya dikemaskini');
                }
            }

            return redirect('/viewoffer')->withErrors(['message' => 'Kemaskini data tidak berjaya ']);

        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    // Functin to delete offer
    public function destroy(Request $request){

        $offerID = $request->get('offerID');

        if(Auth::check()){
            
            if($offerID != null){
                $userID = Auth::user()->id;

                $result = Job_Offer::where([
                    ['status', 1],
                    ['offer_id', $offerID],
                    ['user_id', $userID],
                ])
                ->update([
                    'status' => 0,
                    'updated_at' => date('Y-m-d H:i:s'),
                ]);

                if($result){

                    // Get the current description
                    $currentDesc = Application::where('offer_id', $offerID)
                    ->value('description');

                    // Decode the JSON to an associative array
                    $descArray = json_decode($currentDesc, true);

                    // Update the 'reason' field
                    $descArray['reason'] = "Permohonan ditolak secara automatik kerana pekerjaan telah dipadam";

                    // Encode the array back to JSON
                    $newDesc = json_encode($descArray);

                    $update = Application::where('offer_id', $offerID)
                    ->update([
                        'description' => $newDesc,
                        'approval_status' => 0,
                        'status' => 0,
                    ]);

                    $this->notifyAllUser($offerID);

                    return redirect('/viewoffer')->with('success', 'Data berjaya dikemaskini');
                }
            
            }

            return redirect('/viewoffer')->withErrors(['message' => 'Kemaskini data tidak berjaya ']);

        }

        return redirect('/login')->withErrors(['message' => 'Sila log masuk']);

    }

    // Function to get list of job offers
    public function getUpdatedOffers(){

        $uid = 0;

        if(Auth::check()){
            $uid = Auth::user()->id;
        }

        $allOffers = Job_Offer::where([
            ['job_offers.status', 1],
        ])
        ->join('users as u', 'u.id', '=', 'job_offers.user_id')
        ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
        ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
        ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id')
        ->select(
            'job_offers.*',
            'u.name as username', 
            'u.contactNo as usercontact', 
            'u.email as useremail',
            'j.name as jobname',
            'j.position as jobposition',
            'jt.name as typename',
            'st.name as shiftname',
            DB::raw("DATE(job_offers.updated_at) as updateDate"),
            'job_offers.description->description as description',
            'job_offers.description->reason as reason',
        )
        ->orderBy('job_offers.updated_at', 'desc')
        ->get();

        $enrolledOffers = Application::join('poors as p', 'p.poor_id', '=', 'applications.poor_id')
        ->join('users as u', 'u.id', '=', 'p.user_id')
        ->where([
            ["u.id", $uid],
            ['applications.status', 1],
        ])
        ->select(
            'applications.approval_status',
            'applications.description->reason as reason',
            'applications.description->description as description',
            'applications.offer_id as oid',
            'u.id as user_id'
        )
        ->get();

        return response()->json([
            'allOffers' => $allOffers,
            'enrolledOffers' => $enrolledOffers
        ]);        
    }

    // Function to export offers
    public function exportOffers(Request $request){

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
            $status = 1;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            // Handling for retrieve offers based on conditions
            if(isset($userID) && isset($jobID) && isset($state) && isset($status)){

                // User choose Dipadam
                if($state == 4){
                    $status = 0;
                }

            
                $query = Job_Offer::where([
                    ['job_offers.status', $status],
                    ['job_offers.start_date', '>=', $startDate],
                    ['job_offers.start_date', '<=', $endDate],
                ])
                ->join('users as u', 'u.id', '=', 'job_offers.user_id')
                ->leftJoin('users as processed', function($join) {
                    $join->on('processed.id', '=', 'job_offers.approved_by')
                            ->whereNotNull('job_offers.approved_by');
                })
                ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
                ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id');

                // If user choose Semua
                if($userID != "all"){
                    $query->where('job_offers.user_id', $userID);
                }

                if($jobID != "all"){
                    $query->where('job_offers.job_id', $jobID);
                }

                // If user choose to view by approval_status
                if($state != 3 && $state != 4){
                    $query = $query->where('job_offers.approval_status', $state);
                }

                $selectedOffers = $query->select(
                    'job_offers.*',
                    'u.name as username', 
                    'u.contactNo as usercontact', 
                    'u.email as useremail',
                    'processed.name as processedname', 
                    'processed.email as processedemail',
                    'j.name as jobname',
                    'j.position as jobposition',
                    'jt.name as typename',
                    'st.name as shiftname',
                    'job_offers.description->description as description',
                    'job_offers.description->reason as reason',
                )
                ->orderBy('job_offers.updated_at', 'desc')
                ->get();

                // Transform the data but keep it as a collection of objects
                $selectedOffers->transform(function ($offer) {

                    if($offer->approved_at == null){
                        $offer->approved_at = $offer->updated_at;
                    }

                    $approved_at = explode(' ', $offer->approved_at);
                    $offer->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];

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

                        $offer->date = $offer->start_date . ' hingga ' . $offer->end_date;
                    }
                    else{
                        $offer->date = '';
                    }

                    $offer->time = $offer->start_time . ' hingga ' . $offer->end_time;
                    
                    $offer->people = $offer->quantity_enrolled . '/' . $offer->quantity . ' orang';

                    return $offer;
                });
            }

            if(isset($selectedOffers)){
                return Excel::download(new ExportOffer($selectedOffers), 
                    'Offers-' . time() . '.xlsx'
                );
            }


        }

        return redirect('/viewoffer')->withErrors(["message" => "Eksport Excel tidak berjaya"]);

    }

    // Email to notify user about the creation of job
    public function notifyUser($offerID){

        $offer = Job_Offer::where('job_offers.offer_id', $offerID)
        ->join('jobs', 'jobs.job_id', '=', 'job_offers.job_id')
        ->first();
        $user = User::where('id', $offer->user_id)->select('username', 'email')->first();

        if($offer->approved_at == null){
            $offer->approved_at = $offer->updated_at;
        }

        $approved_at = explode(' ', $offer->approved_at);
        $offer->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];

        Mail::to($user->email)->send(new NotifyJoinEmail([
            'name' => $user->username,
            'subject' => 'pekerjaan',
            'approval' => $offer->approval_status,
            'offer' => $offer->position,
            'datetime' => $offer->approved_at,
        ]));
    }

    // Email to notify all user about the deletion of job
    public function notifyAllUser($offerID){

        $users = Application::where([
            ['applications.offer_id', $offerID],
            ['applications.approval_status', '>', 0],
            ['applications.status', 1],
        ])
        ->join('poors', 'poors.poor_id', '=', 'applications.poor_id')
        ->join('users', 'users.id', '=', 'poors.user_id')
        ->select(
            'users.email',
            'users.username',
        )
        ->get();

        $offer = Job_Offer::where('job_offers.offer_id', $offerID)
        ->join('jobs', 'jobs.job_id', '=', 'job_offers.job_id')
        ->first();

        dd($offer, $users);

        if($offer->approved_at == null){
            $offer->approved_at = $offer->updated_at;
        }

        $approved_at = explode(' ', $offer->approved_at);
        $offer->approved_at = DateController::parseDate($approved_at[0]) . ' ' . $approved_at[1];

        foreach($users as $user){
            Mail::to($user->email)->send(new NotifyJoinEmail([
                'name' => $user->username,
                'subject' => 'pekerjaan',
                'approval' => 0,
                'offer' => $offer->position,
                'datetime' => $offer->approved_at,
            ]));
        }
    }

}
