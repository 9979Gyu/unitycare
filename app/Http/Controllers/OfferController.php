<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Job_Type;
use App\Models\Job_Offer;
use App\Models\Shift_Type;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    // Function to display list of job offered
    public function index(){

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('offers.index', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }

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
            'startDate' => 'required',
            'description' => 'required',
            'salaryStart' => 'required',
            'salaryEnd' => 'required',
        ];

        $validated = $request->validate($rules);

        if($validated){

            $range = $request->get('salaryStart') . ' - ' . $request->get('salaryEnd');

            $job_offer = new job_offer([
                'job_id' => $request->get('position'),
                'job_type_id' => $request->get('jobType'),
                'shift_type_id' => $request->get('shiftType'),
                'postal_code' => $request->get('postalCode'),
                'state' => $request->get('state'),
                'city' => $request->get('city'),
                'start_date' => $request->get('startDate'),
                'description' => $request->get('description'),
                'status' => 1,
                'salary_range' => $range,
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

            $selectedOffers = DB::table('job_offers as jo')
            ->where([
                ['jo.status', 1],
                ['user_id', Auth::user()->id],
            ])
            ->get();

            if(isset($selectedOffers)){
                $table = Datatables::of($selectedOffers);

                $table->addColumn('action', function ($row) {
                    $token = csrf_token();
                    $btn = '<div class="d-flex justify-content-center">';
                    $btn = $btn . '<a class="deleteAnchor" href="#" id="' . $row->offer_id . '"><span class="badge badge-danger" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a></div>';
                    return $btn;
                });
    
                $table->rawColumns(['action']);
                return $table->make(true);
            }

        }

        $roleNo = Auth::user()->roleID;

        return view('offers.index', compact('roleNo'));
    }
}
