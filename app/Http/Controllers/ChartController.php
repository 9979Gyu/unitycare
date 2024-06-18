<?php

namespace App\Http\Controllers;

use App\Models\Application;
use App\Models\User;
use App\Models\Job_Offer;
use Illuminate\Http\Request;

class ChartController extends Controller
{
    //
    public function barChart(){
        // Replace this with your actual data retrieval logic
        $data = [
            'labels' => ['January', 'February', 'March', 'April', 'May'],
            'data' => [65, 59, 80, 81, 56],
        ];
        return view('charts.people', compact('data'));
    }

    public function offerBarChart(Request $request){

        $userID = $request->get('selectedUser');
        $jobID = $request->get('selectedPosition');
        $state = $request->get('selectedState');
        $status = $request->get('status');

        $query = Job_Offer::where('job_offers.status', $status)
        ->join('users as u', 'u.id', '=', 'job_offers.user_id')
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

        $num = $query
        ->groupBy('j.position')
        ->selectRaw('j.position as labels, COUNT(*) as data')
        ->get();

        return response()->json([
            'labels' => $num->pluck('labels'),
            'data' => $num->pluck('data'),
        ]);
    }

    public function appBarChart(Request $request){
        
        $selectedState = $request->get("selectedState");
        $userID = $request->get("selectedUser");
        $status = $request->get("status");
        $selectedPosition = $request->get("selectedPosition");
        $isSelected = $request->get("isSelected");

        if(isset($selectedPosition)){

            $query = Application::where('applications.status', $status)
            ->join('job_offers as jo', 'jo.offer_id', 'applications.offer_id')
            ->join('jobs as j', 'j.job_id', '=', 'jo.job_id');

            if($userID != "all"){
                $query = $query->where('jo.user_id', $userID);
            }

            if($selectedState != 3){
                $query = $query->where('applications.approval_status', $selectedState);
            }

            if($selectedPosition != "all"){
                $query = $query->where('j.job_id', $selectedPosition);
            }

            if($isSelected == 2){
                $query = $query->where('applications.is_selected', 2);
            }

            $num = $query->groupBy('j.position')
            ->selectRaw('j.position as labels, COUNT(*) as data')
            ->get();

            return response()->json([
                'labels' => $num->pluck('labels'),
                'data' => $num->pluck('data'),
            ]);

        }
    }

    public function peoplePieChart(){
        // Get the value of each type of users
        $num = User::where([
            ['users.status', 1],
            ['users.roleID', '>=', 3]
        ])
        ->join('roles as r', 'r.roleID', '=', 'users.roleID')
        ->groupBy('r.name')
        ->selectRaw('r.name as labels, COUNT(*) as data')
        ->get();

        return response()->json([
            'labels' => $num->pluck('labels'),
            'data' => $num->pluck('data'),
        ]);
    }
}
