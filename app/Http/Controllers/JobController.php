<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;

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
                return redirect('/viewjob')->with('success', 'Berjaya didaftarkan');
            }
        }

        return redirect('/viewjob')->with('error', "Pendaftaran tidak berjaya");
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
            $rid = $request->rid;

            $selectedJobs = DB::table('jobs')
            ->join('job_offers as jo', 'jo.job_id', '=', 'jobs.job_id')
            ->where([
                ['jobs.status', 1],
                ['jo.status', 1]
            ])
            ->select(
                'jobs.job_id',
                'jobs.name as name',
                'jobs.description as description',
                'jobs.position as position',
                DB::raw('count(jo.offer_id) as jobOffersCount')
            )
            ->groupBy('jobs.job_id', 'jobs.name', 'jobs.description', 'jobs.position')
            ->get();

            if(isset($selectedJobs)){
                $table = Datatables::of($selectedJobs);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->job_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                    return $btn;
                });
    
                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }

        $roleNo = Auth::user()->roleID;

        return view('jobs.index', compact('roleNo'));
    }

}
