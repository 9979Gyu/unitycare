<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Shift_Type;
use App\Models\Job_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportJob;
use Maatwebsite\Excel\Facades\Excel;

class JobController extends Controller
{
    // Function to display the list of job created.
    public function index(){
        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('jobs.index', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to display the add job form
    public function create(){

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('jobs.add', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to display the add job form
    public function createType(){

        if(Auth::check() && Auth::user()->roleID == 1){
            $roleNo = Auth::user()->roleID;

            return view('jobs.addType', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to store the data from add form to jobs table in database
    public function store(Request $request){

        $type = $request->get('type');

        if($type == "jobs"){     
            $rules = [
                'name' => 'required',
                'position' => 'required',
                'description' => 'required',
            ];
        }
        else if($type == "job_types"){
            $rules = [
                'name' => 'required|unique:job_types,name',
                'description' => 'required',
            ];
        }
        else{
            $rules = [
                'name' => 'required|unique:shift_types,name',
                'description' => 'required',
            ];
        }
        
        $validated = $request->validate($rules);

        if($validated){

            if($type == "jobs"){
                $data = new job([
                    'name' => ucwords(trim($request->get('name'))),
                    'position' => ucwords(trim($request->get('position'))),
                    'description' => trim($request->get('description')),
                    'status' => 1,
                ]);
            }
            else if($type == "job_types"){
                $data = new job_type([
                    'name' => ucwords(trim($request->get('name'))),
                    'description' => trim($request->get('description')),
                    'status' => 1,
                ]);
            }
            else{
                $data = new shift_type([
                    'name' => ucwords(trim($request->get('name'))),
                    'description' => trim($request->get('description')),
                    'status' => 1,
                ]);
            }

            $result = $data->save();

            if($result){
                if(Auth::user()->roleID <= 2)
                    return redirect('/viewjob')->with('success', 'Berjaya didaftarkan');
                else
                    return redirect('/viewoffer')->with('success', 'Berjaya didaftarkan');
            }
        }

        if(Auth::user()->roleID <= 2)
            return redirect('/viewjob')->with('error', "Pendaftaran tidak berjaya");
        else
            return redirect('/viewoffer')->with('error', 'Pendaftaran tidak berjaya');
    }

    // Function to update the status
    public function update(Request $request)
    {
        $jobID = $request->get('selectedID');
        $type = $request->get('selectedType');

        if($type == "job"){
            $type == "jobs";
        }
        else if($type == "shift"){
            $type == "shift_types";
        }
        else{
            $type == "job_types";
        }

        //
        $update = DB::table($type)
        ->where([
            ['job_id', $jobID],
        ])
        ->update([
            'status' => 1,
        ]);  

        if($result)
            return redirect()->back()->with('success', 'Berjaya dikemaskini');

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dikemaskini"]);
    }

    // Function to remove the job from the display list in index
    public function destroy(Request $request){

        $jobID = $request->get('selectedID');
        $type = $request->get('selectedType');

        if($type == "job"){
            $type == "jobs";
        }
        else if($type == "shift"){
            $type == "shift_types";
        }
        else{
            $type == "job_types";
        }

        $update = DB::table($type)
        ->where([
            ['job_id', $jobID],
            ['status', 1],
        ])
        ->update([
            'status' => 0,
        ]);  

        if($result)
            return redirect()->back()->with('success', 'Berjaya dipadam');

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
    }

    // Function to get list of jobs from database and display in datatable
    public function getJobsDatatable(Request $request)
    {
        if(request()->ajax()){
            $rid = $request->get('rid');
            $type = $request->get('selectedType');

            // Handling for retrieve jobs based on type
            if(isset($rid) && isset($type)){

                if($type == "job"){
                    $selectedItem = Job::select(
                        'job_id as id',
                        DB::raw('CONCAT(name, " - ", position) as name'),
                        'description',
                        'status'
                    )
                    ->withCount('jobOffers')
                    ->groupBy('job_id', 'name', 'description', 'position', 'status')
                    ->orderBy('name')
                    ->get();

                }
                else if($type == "shift"){
                    $selectedItem = Shift_Type::select(
                        'shift_type_id as id',
                        'name',
                        'description',
                        'status'
                    )
                    ->withCount('jobOffers')
                    ->groupBy('shift_type_id', 'name', 'description', 'status')
                    ->orderBy('name')
                    ->get();
                }
                else{
                    $selectedItem = Job_Type::select(
                        'job_type_id as id',
                        'name',
                        'description',
                        'status'
                    )
                    ->withCount('jobOffers')
                    ->groupBy('job_type_id', 'name', 'description', 'status')
                    ->orderBy('name')
                    ->get();
                }   

            }


            if(isset($selectedItem)){
                $table = Datatables::of($selectedItem);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    
                    if($row->status == 1){
                        $btn .= '<div>';
                        $btn .= '<a class="deleteAnchor" href="#" id="' . $row->id . '"><span class="btn btn-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                        $btn .= '</div>';
                    }
                    else{
                        $btn .= '<div>';
                        $btn .= '<a class="updateAnchor" href="#" id="' . $row->id . '"><span class="btn btn-warning"> Aktif </span></a>';
                        $btn .= '</div>';
                    }

                    return $btn;
                });
    
                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }

        $roleNo = Auth::user()->roleID;

        return view('jobs.index', compact('roleNo'));
    }

    // Function to export program info
    public function exportJobs(Request $request){
        
        // Validate the request data
        $rules = [
            'roleID' => 'required',
            'type' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){
            // Retrieve the validated data
            $roleID = $request->get('roleID');
            $type = $request->get('type');

            return Excel::download(new ExportJob(
                $roleID, $type), 
                ucwords($type) . '-' . time() . '.xlsx'
            );
        }
        
        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }
}
