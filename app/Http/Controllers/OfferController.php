<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\User;
use App\Models\Application;
use App\Models\Job_Type;
use App\Models\Job_Offer;
use App\Models\Shift_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\ExportOffer;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use App\Mail\NotifyJoinEmail;

class OfferController extends Controller
{
    // Email to notify user about the creation of job
    public function notifyUser($offerID){

        $offer = Job_Offer::where('job_offers.offer_id', $offerID)
        ->join('jobs', 'jobs.job_id', '=', 'job_offers.job_id')
        ->first();
        $user = User::where('id', $offer->user_id)->select('username', 'email')->first();

        $date = explode($offer->approved_at, " ");

        Mail::to($user->email)->send(new NotifyJoinEmail([
            'name' => $user->username,
            'subject' => 'pekerjaan',
            'approval' => $offer->approval_status,
            'offer' => $offer->position,
            'datetime' => DateController::parseDate($date[0]) . ' ' . $date[1],
        ]));
    }

    // Function to display list of job offered
    public function index(){

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            $uid = Auth::user()->id;

            if($roleNo == 1 || $roleNo == 2){
                return view('offers.process', compact('roleNo', 'uid'));
            }
            elseif($roleNo == 3 || $roleNo == 5){
                return view('offers.index', compact('roleNo', 'uid'));
            }
        }
        
        return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

    }

    // Function to display add offer form
    public function create(){
        
        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            $jobTypes = Job_Type::where('status', 1)->get();
            $shiftTypes = Shift_Type::where('status', 1)->get();
            return view('offers.add', compact('roleNo', 'jobTypes', 'shiftTypes'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }

    }

    // Function to store offer into job_offers table in database
    public function store(Request $request){

        $jobTypes = $request->get('jobType');

        if($jobTypes != 1){
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

                return redirect('/viewoffer')->with('success', 'Berjaya didaftarkan');
            }
        }

        return redirect('/viewoffer')->with('error', "Pendaftaran tidak berjaya");
    }

    // Function to edit offer
    public function edit($id){

        $offer = Job_Offer::where([
            ['job_offers.offer_id', $id],
            ['job_offers.status', 1],
            ['job_offers.approval_status', '<', 2],
        ])
        ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
        ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
        ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id')
        ->select(
            'job_offers.*',
            'j.name as jobname',
            'j.position as jobposition',
            'jt.name as typename',
            'st.name as shiftname',
            DB::raw("DATE(job_offers.updated_at) as updateDate"),
            'job_offers.description->description as description',
            'job_offers.description->reason as reason',
        )
        ->first();

        if(Auth::check() && Auth::user()->id == $offer->user_id){
            return view('offers.edit', compact('offer'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to update edited offer into job_offers table in database
    public function update(Request $request){

        $jobTypes = $request->get('jobType');

        if($jobTypes != 1){
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

        return redirect('/viewoffer')->with('error', "Kemaskini data tidak berjaya");

    }

    // Function to remove offer from database
    public function destroy(Request $request){

        $update = DB::table('job_offers')
        ->where([
            ['offer_id', $request->selectedID],
            ['status', 1],
        ])
        ->update([
            'status' => 0,
        ]);  

        if($update){
            // send email to verify all applied and approval_status is 1 user
            return redirect()->back()->with('success', 'Berjaya dipadam');
        }
            

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);

    }

    // Function to get list of jobs from database
    public function getJobs(){
        $jobs = Job::select('name')
        ->where('status', 1)
        ->distinct('name')
        ->get();

        return response()->json($jobs);
    }

    public function getJobsByUser(Request $request){
        $userID = $request->get('selectedUser');
        $jobs = Job_Offer::join('jobs', 'jobs.job_id', '=', 'job_offers.job_id')
        ->where([
            ['jobs.status', 1],
            ['job_offers.status', 1],
            ['job_offers.user_id', $userID]
        ])
        ->select(
            'jobs.job_id',
            'jobs.name', 
        )
        ->distinct('jobs.name')
        ->get();

        return response()->json($jobs);
    }

    // Function to get list of position related to the selected jobs from database
    public function getPositions(Request $request){
        $jobName = $request->get('jobName');
        $userID = $request->get('userID');

        $positions = Job::where([
            ['jobs.name', 'like', '%' . $jobName . '%'],
            ['jobs.status', 1]
        ])
        ->join('job_offers', 'job_offers.job_id', '=', 'jobs.job_id')
        ->where([
            ['job_offers.user_id', $userID]
        ])
        ->get();

        $positions = $positions->unique('job_id');

        return response()->json($positions);
    }

    public function getAllPositions(Request $request){
        $jobName = $request->get('jobName');

        $positions = Job::where([
            ['jobs.name', 'like', '%' . $jobName . '%'],
            ['jobs.status', 1]
        ])
        ->get();

        return response()->json($positions);
    }

    // Function to get list of offers from database and display in datatable
    public function getOffersDatatable(Request $request)
    {
        if(request()->ajax()){
            $rid = $request->get('rid');
            $state = $request->get('selectedState');
            $status = $request->get('status');

            // Handling for retrieve programs based on approval state and program type
            if(isset($rid) && isset($state) && isset($status)){

                $query = Job_Offer::where([
                    ['job_offers.status', $status],
                ])
                ->join('users as u', 'u.id', '=', 'job_offers.user_id')
                ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
                ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id');

                if($state != 3){
                    $query = Job_Offer::where([
                        ['job_offers.status', $status],
                        ['job_offers.approval_status', $state],
                    ])
                    ->join('users as u', 'u.id', '=', 'job_offers.user_id')
                    ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
                    ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
                    ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id');
                }

                $selectedOffers = $query
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
            }

            if(isset($selectedOffers)){

                $table = Datatables::of($selectedOffers);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    
                    if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                        if($row->approval_status == 1){
                            // Program is pending approval
                            $btn = $btn . '<a class="approveAnchor m-1" href="#" id="' . $row->offer_id . '"><span class="badge badge-success" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a>';
                            $btn = $btn . '<a class="declineAnchor m-1" href="#" id="' . $row->offer_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';
                        }
                        else{
                            $btn = $btn . '<a class="declineAnchor" href="#" id="' . $row->offer_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';

                        }
                    }

                    $btn = $btn . '</div>';

                    return $btn;

                });
    
                $table->rawColumns(['action']);

                return $table->make(true);
            }

        }

        $roleNo = Auth::user()->roleID;

        return view('offers.index', compact('roleNo'));
    }

    public function getOffersByPositionDatatable(Request $request)
    {
        if(request()->ajax()){
            $roleID = $request->get('rid');
            $userID = $request->get('selectedUser');
            $jobID = $request->get('selectedPosition');
            $state = $request->get('selectedState');
            $status = $request->get('status');

            // Handling for retrieve programs based on approval state and program type
            if(isset($roleID) && isset($userID) && isset($jobID) && isset($state) && isset($status)){

                $query = Job_Offer::where([
                    ['job_offers.status', $status],
                    ['job_offers.user_id', $userID],
                    ['job_offers.job_id', $jobID]
                ])
                ->join('users as u', 'u.id', '=', 'job_offers.user_id')
                ->leftJoin('users as processed', function($join) {
                    $join->on('processed.id', '=', 'job_offers.approved_by')
                         ->whereNotNull('job_offers.approved_by');
                })
                ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
                ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id');

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
                        $approval = "Ditolak";
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
                    $offer->start_date = DateController::parseDate($startDate);
                    $offer->start = $offer->start_date . ' ' . $offer->start_time;

                    $endDate = $offer->end_date;
                    $offer->end_date = DateController::parseDate($endDate);
                    $offer->end = $offer->end_date . ' ' . $offer->end_time;

                    $offer->people = $offer->quantity_enrolled . '/' . $offer->quantity . ' orang';

                    return $offer;
                });
            }

            if(isset($selectedOffers)){

                $table = Datatables::of($selectedOffers);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<a href="/joinoffer/' . $row->offer_id . '"><span class="badge badge-primary m-1"> Lihat </span></a>';
                    
                    //  Is admin or staff
                    if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                        
                        if($row->approval_status == 1){
                            // Program is pending approval
                            $btn .= '<div>';
                            $btn = $btn . '<a class="approveAnchor" href="#" id="' . $row->offer_id . '"><span class="badge badge-success m-1" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a>';
                            $btn = $btn . '<a class="declineAnchor" href="#" id="' . $row->offer_id . '"><span class="badge badge-danger m-1" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';
                            $btn .= '</div>';
                        }
                        else{
                            $btn .= '<div>';
                            $btn = $btn . '<a class="declineAnchor" href="#" id="' . $row->offer_id . '"><span class="badge badge-danger m-1" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a>';
                            $btn .= '</div>';

                        }
                    }
                    else{
                        if($row->user_id == Auth::user()->id){
                            if($row->approval_status == 1){
                                
                                // Program is pending approval
                                $btn .= '<div>';
                                $btn = $btn . '<a href="/editoffer/' . $row->offer_id . '"><span class="badge badge-warning m-1"> Kemaskini </span></a>';
                                $btn .= '</div>';
                            }
                        }
                        $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->offer_id . '"><span class="badge badge-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                    }

                    $btn = $btn . '</div>';

                    return $btn;

                });
    
                $table->rawColumns(['action']);

                return $table->make(true);
            }

        }
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

    // Function to get list of job offers based on the keyword or city and state
    public function getUpdatedOffersByKeyword(Request $request){

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

        return response()->json([
            'allOffers' => $allOffers
        ]);        
    }

    public function updateApproval(Request $request){
        $id = $request->get("selectedID");
        
        // Get the current date and time
        $currentDateTime = Carbon::now();

        if(isset($id) && isset($currentDateTime)){
            // Update the program details
            $update = Job_Offer::where([
                ['offer_id', $id],
                ['status', 1],
            ])
            ->update([
                'approval_status' => 2,
                'approved_by' => Auth::user()->id, 
                'approved_at' => $currentDateTime,
            ]);

            // If successfully update the program
            if($update){

                $this->notifyUser($id);

                // direct user to view program page with success messasge
                return redirect('/viewoffer')->with('success', 'Data berjaya dikemaskini');
            }
        }

        // direct user to view program page with error messasge
        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);

    }

    public function declineApproval(Request $request){

        // Get the current date and time
        $currentDateTime = Carbon::now();

        $id = $request->get("selectedID");

        // Get the current description
        $currentDesc = Job_Offer::where('offer_id', $id)
        ->value('description');

        // Decode the JSON to an associative array
        $descArray = json_decode($currentDesc, true);

        // Update the 'reason' field
        $descArray['reason'] = $request->get('reason');

        // Encode the array back to JSON
        $newDesc = json_encode($descArray);

        if(isset($id)){
            // Update the program details
            $update = Job_Offer::where([
                ['offer_id', $id],
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
                $this->notifyUser($id);
                // direct user to view program page with success messasge
                return redirect('/viewoffer')->with('success', 'Data berjaya dikemaskini');
            }
        }

        // direct user to view program page with error messasge
        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);

    }

    // Function to get all approved offers
    public function getApprovedOffers(){
        $allOffers = Job_Offer::where([
            ['job_offers.status', 1],
            ['job_offers.approval_status', 2]
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
            'u.sector_id as sectorid',
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

        return $allOffers;
    }

    // Function to export offer info
    public function exportOffers(Request $request){
        
        // Validate the request data
        $rules = [
            'roleID' => 'required',
            'statusFilter' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            $state = $request->get('statusFilter');
            $status = 1;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if($state == 4){
                $status = 0;
            }

            return Excel::download(new ExportOffer(
                $roleID, $state, $status, $startDate, $endDate), 
                'Offers-' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

}

