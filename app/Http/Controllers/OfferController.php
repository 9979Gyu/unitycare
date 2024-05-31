<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Application;
use App\Models\Job_Type;
use App\Models\Job_Offer;
use App\Models\Shift_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;

class OfferController extends Controller
{
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
        $rules = [
            'position' => 'required',
            'jobType' => 'required',
            'shiftType' => 'required',
            'postalCode' => 'required',
            'state' => 'required',
            'city' => 'required',
            'description' => 'required',
            'salaryStart' => 'required',
            'salaryEnd' => 'required',
        ];

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
                'postal_code' => $request->get('postalCode'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'description' => json_encode($desc),
                'status' => 1,
                'min_salary' => $request->get('salaryStart'),
                'max_salary' => $request->get('salaryEnd'),
                'user_id' => Auth::user()->id,
                'approval_status' => 1,
            ]);

            $result = $job_offer->save();

            if($result){
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

        $rules = [
            'position' => 'required',
            'jobType' => 'required',
            'shiftType' => 'required',
            'postalCode' => 'required',
            'state' => 'required',
            'city' => 'required',
            'description' => 'required',
            'salaryStart' => 'required',
            'salaryEnd' => 'required',
        ];

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
                'postal_code' => $request->get('postalCode'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'description' => json_encode($desc),
                'status' => 1,
                'min_salary' => $request->get('salaryStart'),
                'max_salary' => $request->get('salaryEnd'),
                'user_id' => Auth::user()->id,
                'approval_status' => 1,
            ]);

            if($result){
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

        if($update)
            return redirect()->back()->with('success', 'Berjaya dipadam');

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

    // Function to get list of position related to the selected jobs from database
    public function getPositions(Request $request){
        $jobName = $request->get('jobName');

        $positions = Job::where([
            ['name', 'like', '%' . $jobName . '%'],
            ['status', 1]
        ])
        ->get();

        return response()->json($positions);
    }

    // Function to get list of offers from database and display in datatable
    public function getOffersDatatable(Request $request)
    {
        if(request()->ajax()){
            $rid = $request->rid;

            $selectedOffers = Job_Offer::where([
                ['job_offers.status', 1],
                ['job_offers.approval_status', 1],
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
                // direct user to view program page with success messasge
                return redirect('/viewoffer')->with('success', 'Data berjaya dikemaskini');
            }
        }

        // direct user to view program page with error messasge
        return redirect()->back()->withErrors(['message' => "Data tidak berjaya dikemaskini"]);

    }

    // Function to get all approved offers
    public static function getApprovedOffers(){
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
}

