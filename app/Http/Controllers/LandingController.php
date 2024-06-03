<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Job;
use App\Models\Job_Offer;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Builder;
use Auth;

class LandingController extends Controller{

    public function info(){
        return view('landings.info');
    }

    public function index(){
        
        // Check if user logged in and is B40/OKU
        if(Auth::check() && Auth::user()->roleID == 5){

            // Get logged in user's poor id
            $poorId = Auth::user()->poor->poor_id;

            // Retrieve sectors based on approved application which the user has made
            $sectors = Sector::whereHas('organizations.jobOffers.applications', function ($query) use ($poorId) {
                $query->where('approval_status', 2)
                ->where('status', 1)
                ->where('poor_id', $poorId);
            })
            ->with([
                'organizations.jobOffers' => function ($query) use ($poorId) {
                    $query->whereHas('applications', function ($query) use ($poorId) {
                        $query->where('approval_status', 2)
                        ->where('status', 1)
                        ->where('poor_id', $poorId);
                    })
                    ->with([
                        'shiftType',
                        'jobType',
                        'organization',
                        'applications' => function ($query) use ($poorId){
                            $query->where('approval_status', 2)
                            ->where('status', 1)
                            ->where('poor_id', $poorId);
                        }
                    ]);
                }
            ])
            ->get();
        }
        else{
            // Retrieve sectors that have associated job offers
            $sectors = Sector::whereHas('organizations.jobOffers', function ($query) {
                $query->where('approval_status', 2);
            })
            ->with([
                'organizations.jobOffers' => function ($query) {
                    $query->with([
                        'shiftType',
                        'jobType',
                        'organization'
                    ]);
                },
                // Ensures that the organization related to jobOffers is eagerly loaded
                'organizations.jobOffers.organization'
            ])
            ->get();
        }

        return view('landings.index', compact('sectors'));
    }

    public function search(Request $request)
    {
        $searchQuery = ucwords($request->get('query'));
        $searchOption = $request->get('option');

        if(isset($searchQuery) && isset($searchOption)){

            if($searchOption == "program"){
                // $results = Program::whereHas('organization', function ($query) use ($searchQuery) {
                //     $query->where('name', 'like', '%' . $searchQuery . '%')
                //           ->where('status', 1)
                //           ->where('approved_status', 2);
                // })
                // ->with('organization')
                // ->get();
                $results = Program::where('name', 'like', '%' . $searchQuery . '%')
                          ->where('status', 1)
                          ->where('approved_status', 2)
                          ->with('organization')
                          ->get();
            }
            else{
                $results = Job::whereHas('jobOffers.organization', function ($query) use ($searchQuery) {
                    $query->where('position', 'like', '%' . $searchQuery . '%')
                          ->where('status', 1);
                })
                ->with(['jobOffers' => function ($query) {
                    $query->where('approval_status', 2)
                          ->with(['job', 'organization']);
                }])
                ->get();

            }
            
            return response()->json($results);
        }
    }

    // Function to return list of programs display in landings page
    public function getPrograms(Request $request){

        $events = [];
        
        // Call function from ProgramController to get all programs
        $programs = ProgramController::getProgramsByApprovedStatus();

        if(isset($programs)){
            // Loop to filter programs
            foreach ($programs as $program) {
                // Program is approved by staff
                if($program->approved_status == 2){
                    $events[] = [
                        'title' => $program->name, 
                        'start' => $program->start_date,
                        'id' => $program->program_id,
                    ];
                }
            } 
        }

        // Return events data as JSON
        return response()->json($events);

    }

    // Function to return the number of position in each sector
    public function getCountPosition(){
        $events = [];
        $offers = OfferController::getApprovedOffers();
        $sectors = Sector::where('status', 1)->get();

        $sectorSums = [];

        if(isset($offers)){
            
            foreach ($offers as $offer) {

                // Access the user's sector attribute within the offer
                $sectorID = $offer->sectorid;

                // Count the offer by sector
                if (isset($sectorSums[$sectorID])) {
                    $sectorSums[$sectorID]++;
                } 
                else {
                    $sectorSums[$sectorID] = 1;
                }
                
            }

            foreach ($sectors as $sector) {

                $tempSec = $sector->sector_id;

                // If the sector contains offer, save data into events variable
                if(isset($sectorSums[$tempSec])){
                    $events[] = [
                        'sectorid' => $tempSec,
                        'sectorname' => $sector->name,
                        'offercount' => $sectorSums[$tempSec],
                    ];
                }

            }

            // Return events data as JSON
            return response()->json(['events' => $events]);

        }

    }

    // Function to return the number of offer in each sector
    public function getCountOffer(Request $request){
        $events = [];
        $offers = OfferController::getApprovedOffers();
        $positionSums = [];
        $sectorID = $request->get("sectorID");

        if (isset($offers)) {
            
            // Count the offers by job position
            foreach ($offers as $offer) {

                if($offer->sectorid == $sectorID){
                    $positionName = $offer->jobposition;

                    if (isset($positionSums[$positionName])) {
                        $positionSums[$positionName]++;
                    } else {
                        $positionSums[$positionName] = 1;
                    }
                }

            }

            // Populate the events array with the job positions and their counts
            foreach ($positionSums as $positionName => $count) {
                $events[] = [
                    'jobposition' => $positionName,
                    'offercount' => $count,
                ];
            }

            // Return the events data along with the total count as JSON
            return response()->json([
                'events' => $events,
            ]);
        }

    }

    // Function to return list of jobs to display in landings page
    public function getJobs(Request $request){
        $events = [];
        $offers = OfferController::getApprovedOffers();
        $sectorID = $request->get("sectorID");
        $position = $request->get("positionName");
    
        if(isset($offers)){
            foreach ($offers as $offer) {
                if(!Auth::check() && isset($sectorID) && isset($position)){
                    if($offer->sectorid == $sectorID && $offer->jobposition == $position && $offer->approval_status == 2){
                        $events[] = [
                            'jobname' => $offer->jobname,
                            'jobposition' => $offer->jobposition,
                            'offer_id' => $offer->offer_id,
                            'min_salary' => $offer->min_salary,
                            'max_salary' => $offer->max_salary,
                            'shiftname' => $offer->shiftname,
                            'typename' => $offer->typename,
                            'username' => $offer->username,
                        ];
                    }
                } else {
                    $events[] = [
                        'jobname' => $offer->jobname,
                        'jobposition' => $offer->jobposition,
                        'offer_id' => $offer->offer_id,
                        'min_salary' => $offer->min_salary,
                        'max_salary' => $offer->max_salary,
                        'shiftname' => $offer->shiftname,
                        'typename' => $offer->typename,
                        'username' => $offer->username,
                    ];
                }
            }
        }
        return response()->json(['events' => $events]);
    }
    
}
