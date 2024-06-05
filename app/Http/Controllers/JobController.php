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

    // Function to store the data from add form to jobs table in database
    public function store(Request $request){
    
        $rules = [
            'name' => 'required',
            'position' => 'required',
            'description' => 'required',
        ];
        
        $validated = $request->validate($rules);

        if($validated){
            $job = new job([
                'name' => ucwords($request->get('name')),
                'position' => ucwords($request->get('position')),
                'description' => $request->get('description'),
                'status' => 1,
            ]);

            $result = $job->save();

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

    // Function to remove the job from the display list in index
    public function destroy(Request $request)
    {
        //
        $update = DB::table('jobs')
        ->where([
            ['job_id', $request->selectedID],
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
                        'description'
                    )
                    ->withCount('jobOffers')
                    ->where('status', 1)
                    ->groupBy('job_id', 'name', 'description', 'position')
                    ->orderBy('name')
                    ->get();

                }
                else if($type == "shift"){
                    $selectedItem = Shift_Type::select(
                        'shift_type_id as id',
                        'name',
                        'description',
                    )
                    ->withCount('jobOffers')
                    ->where('status', 1)
                    ->groupBy('shift_type_id', 'name', 'description')
                    ->orderBy('name')
                    ->get();
                }
                else{
                    $selectedItem = Job_Type::select(
                        'job_type_id as id',
                        'name',
                        'description',
                    )
                    ->withCount('jobOffers')
                    ->where('status', 1)
                    ->groupBy('job_type_id', 'name', 'description')
                    ->orderBy('name')
                    ->get();
                }   

            }


            if(isset($selectedItem)){
                $table = Datatables::of($selectedItem);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    // $btn = $btn . '<a href="/editprogram/' . $row->id . '"><span class="badge badge-warning"> Kemaskini </span></a></div>';
                    $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
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
