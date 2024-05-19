<?php

namespace App\Http\Controllers;

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

class ApplicationController extends Controller
{

    public function create($id){

        if(Auth::check() && isset($id)){
            $offer = Job_Offer::where([
                ['job_offers.offer_id', $id],
                ['job_offers.status', 1],
                ['job_offers.approval_status', 2],
            ])
            ->join('users as u', 'u.id', '=', 'job_offers.user_id')
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
                'u.name as username', 
                'u.contactNo as usercontact', 
                'u.email as useremail',
            )
            ->first();
    
            $applicationExist = Application::join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
            ->join('poors as p', 'p.poor_id', '=', 'applications.poor_id')
            ->where([
                ['jo.offer_id', $id],
                ['applications.status', 1],
                ['jo.approval_status', 2],
                ['p.user_id', Auth::user()->id]
            ])
            ->count();
    
            return view('applications.add', compact('offer', 'applicationExist'));
        }

        return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);

    }

    public function store(Request $request){
    
        $offerID = $request->get("offer_id");
        $uid = Auth::user()->id;

        // Get the current date
        $currentDateTime = date('Y-m-d H:i:s.u');

        $poorID = Poor::where([
            ['user_id', $uid],
            ['status', 1]
        ])
        ->value("poor_id");

        if(isset($offerID) && isset($poorID)){
            $application = new Application([
                'applied_date' => $currentDateTime,
                'offer_id' => $offerID,
                'poor_id' => $poorID,
                'status' => 1,
                'approval_status' => 1,
            ]);

            $result = $application->save();

            if($result){
                return redirect('/viewoffer')->with('success', 'Berjaya didaftarkan');
            }
        }

        return redirect('/viewoffer')->withErrors(['message' => "Pendaftaran tidak berjaya"]);

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
    
            if($result)
                return redirect()->back()->with('success', 'Berjaya dipadam');
    
        }

        return redirect()->back()->withErrors(["message" => "Tidak berjaya dipadam"]);
    }
}
