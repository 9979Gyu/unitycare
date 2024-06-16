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

        dd($offer);

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

    public function index(){
        if(Auth::check()){

            $roleNo = Auth::user()->roleID;

            if($roleNo == 1){
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
            else if($roleNo == 3){
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

                return view('applications.index', compact('roleNo', 'users'));
            }

        }
        return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
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
                    return view('offers.index', compact('roleNo', 'uid'));
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
            $rid = $request->get("rid");
            $selectedState = $request->get("selectedState");
            $selectedPosition = $request->get("positionID");
            $userID = Auth::user()->id;

            if(isset($selectedPosition)){

                if(isset($selectedState) && $selectedState != 3){

                    $selectedApplication = Application::join('poors', 'poors.poor_id', '=', 'applications.poor_id')
                    ->join('users', 'users.id', '=', 'poors.user_id')
                    ->join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
                    ->join('jobs as j', 'j.job_id', '=', 'jo.job_id')
                    ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                    ->join('education_levels as el', 'el.edu_level_id', '=', 'poors.education_level')
                    ->where([
                        ['applications.status', 1],
                        ['jo.status', 1],
                        ['j.status', 1],
                        ['j.job_id', $selectedPosition],
                        ['applications.approval_status', $selectedState],
                        ['jo.user_id', $userID],
                    ])
                    ->select(
                        'applications.*',
                        'applications.description->description as description',
                        'applications.description->reason as reason',
                        'users.name as username',
                        'users.email as useremail',
                        'users.contactNo as usercontact',
                        'poors.disability_type',
                        'dt.name as category',
                        'el.name as edu_level',
                        'j.position as position'
                    )
                    ->orderBy("applications.applied_date", "asc")
                    ->get();
                }
                else{

                    $selectedApplication = Application::join('poors', 'poors.poor_id', '=', 'applications.poor_id')
                    ->join('users', 'users.id', '=', 'poors.user_id')
                    ->join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
                    ->join('jobs as j', 'j.job_id', '=', 'jo.job_id')
                    ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                    ->join('education_levels as el', 'el.edu_level_id', '=', 'poors.education_level')
                    ->where([
                        ['applications.status', 1],
                        ['jo.status', 1],
                        ['j.status', 1],
                        ['jo.user_id', $userID],
                        ['j.job_id', $selectedPosition],
                    ])
                    ->select(
                        'applications.*',
                        'applications.description->description as description',
                        'applications.description->reason as reason',
                        'users.name as username',
                        'users.email as useremail',
                        'users.contactNo as usercontact',
                        'poors.disability_type',
                        'dt.name as category',
                        'el.name as edu_level',
                        'j.position as position'
                    )
                    ->orderBy("applications.applied_date", "asc")
                    ->get();
                }
            }

            if(isset($selectedApplication)){

                $table = Datatables::of($selectedApplication);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    if(Auth::user()->roleID == 3){
                        if($row->approval_status == 1){
                            // pending approval
                            $btn = $btn . '<div><a class="approveAnchor m-1" href="#" id="' . $row->application_id . '"><span class="badge badge-success" data-bs-toggle="modal" data-bs-target="#approveModal"> Lulus </span></a></div>';
                            $btn = $btn . '<div><a class="declineAnchor m-1" href="#" id="' . $row->application_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#declineModal"> Tolak </span></a></div>';
                        }
                    }
                    $btn .= '</div>';
                
                    return $btn;
                });

                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }

        $roleNo = Auth::user()->roleID;

        return view('applications.index', compact('roleNo'));
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
            'roleID' => 'required',
            'statusFilter' => 'required',
            'position' => 'required',
            'job' => 'required',
            'startDate' => 'required',
            'endDate' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            $state = $request->get('statusFilter');
            $status = 1;
            $selectedPosition = $request->get("position");
            $userID = Auth::user()->id;
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            if($state == 4){
                $status = 0;
            }

            $filename = Job::where('job_id', $selectedPosition)->value("position");

            return Excel::download(new ExportApplication(
                $roleID, $state, $status, $selectedPosition, $userID, $startDate, $endDate), 
                'Permohonan Kerja (' . $filename . ') - ' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }
}
