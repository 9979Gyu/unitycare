<?php

namespace App\Http\Controllers;

use App\Models\Job;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;

class JobController extends Controller
{
    //
    public function index(){
        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('jobs.index', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to create the job name and position
    public function create(){

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('jobs.add', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    public function store(Request $request){
    
        $rules = [
            'name' => 'required',
            'position' => 'required',
            'description' => 'required',
        ];
        
        $validated = $request->validate($rules);

        if(validated){
            $job = new job([
                'name' => $request->get('name'),
                'position' => $request->get('position'),
                'description' => $request->get('description'),
                'status' => 1,
            ]);

            $result = $job->save();

            if($result){
                return redirect('/viewprogram')->with('success', 'Berjaya didaftarkan');
            }
            else{
                return redirect('/viewprogram')->withErrors(['message' => "Pendaftaran tidak berjaya"]);
            }
        }
    }

    // Function to edit the job
    public function edit(){

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('jobs.add', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

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

    // Function to get list of jobs
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
                'jobs.name as jobName',
                'jobs.description as jobDescription',
                'jobs.position as jobPosition',
                DB::raw('count(jo.offer_id) as jobOffersCount')
            )
            ->groupBy('jobs.job_id')
            ->get();

            if($selectedJobs->isEmpty()){
                return response()->json(['message' => 'No jobs available'], 200);
            }

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

        $roleNo = Auth::user()->roleID;

        return view('jobs.index', compact('roleNo'));
    }

}
