<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Job;
use App\Models\Job_Offer;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Auth;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use GuzzleHttp\Client; // to get response from external api

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
                $query->where([
                    ['approval_status', 2],
                    ['status', 1],
                    ['poor_id', $poorId]
                ]);
            })
            ->with([
                'organizations.jobOffers' => function ($query) use ($poorId) {
                    $query->whereHas('applications', function ($query) use ($poorId) {
                        $query->where([
                            ['approval_status', 2],
                            ['status', 1],
                            ['poor_id', $poorId]
                        ]);
                    })
                    ->with([
                        'shiftType',
                        'jobType',
                        'organization',
                        'applications' => function ($query) use ($poorId){
                            $query->where([
                                ['approval_status', 2],
                                ['status', 1],
                                ['poor_id', $poorId]
                            ]);
                        }
                    ]);
                }
            ])
            ->get();
        }
        else{
            // Retrieve sectors that have associated job offers
            $sectors = Sector::whereHas('organizations.jobOffers', function ($query) {
                $query->where([
                    ['approval_status', 2],
                    ['status', 1],
                ]);
            })
            ->with([
                'organizations.jobOffers' => function ($query) {
                    $query->with([
                        'shiftType',
                        'jobType',
                        'organization'
                    ])
                    ->where([
                        ['approval_status', 2],
                        ['status', 1],
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
        $searchQuery = $request->get('query');

        if(isset($searchQuery)){

            $programQuery = DB::table('programs')
                ->join('users', 'users.id', '=', 'programs.user_id')
                ->where([
                    ['programs.status', 1],
                    ['programs.approved_status', 2],
                ])
                ->select(
                    'programs.*',
                    'users.name as username'
                );

            $programNameQuery = $programQuery->clone()
                ->where('programs.name', 'like', '%' . $searchQuery . '%')
                ->get();

            $programVenueQuery = $programQuery->clone()
                ->where('programs.venue', 'like', '%' . $searchQuery . '%')
                ->get();

            $programStartQuery = $programQuery->clone()
                ->where('programs.start_date', 'like', '%' . $searchQuery . '%')
                ->get();

            $programUserQuery = $programQuery->clone()
                ->where([
                    ['users.status', 1],
                    ['users.name', 'like', '%' . $searchQuery . '%']
                ])
                ->get();

            // Convert the result into array and merge the results into $programs
            $programs = array_merge($programNameQuery->toArray(), $programVenueQuery->toArray());
            $programs = array_merge($programs, $programStartQuery->toArray());
            $programs = array_merge($programs, $programUserQuery->toArray());

            
            $uniqueOffers = [];
            $offers = [];

            $query = DB::table('job_offers')
            ->join('jobs', 'jobs.job_id', '=', 'job_offers.job_id')
            ->join('shift_types', 'shift_types.shift_type_id', '=', 'job_offers.shift_type_id')
            ->join('job_types', 'job_types.job_type_id', '=', 'job_offers.job_type_id')
            ->join('users', 'users.id', '=', 'job_offers.user_id')
            ->where([
                ['job_offers.status', 1],
                ['job_offers.approval_status', 2],
            ])
            ->select(
                'job_offers.offer_id',
                'job_offers.min_salary',
                'job_offers.max_salary',
                'jobs.name as jobname',
                'jobs.position as jobposition',
                'shift_types.name as shiftname',
                'job_types.name as typename',
                'users.name as username'
            );
            
            $nameResults = $query->clone()
            ->where('jobs.name', 'like', '%' . $searchQuery . '%')
            ->get();

            $positionResults = $query->clone()
            ->where('jobs.position', 'like', '%' . $searchQuery . '%')
            ->get();

            $stateResults = $query->clone()
            ->where('job_offers.state', 'like', '%' . $searchQuery . '%')
            ->get();

            $cityResults = $query->clone()
            ->where('job_offers.city', 'like', '%' . $searchQuery . '%')
            ->get();

            $typeResults = $query->clone()
            ->where('job_types.name', 'like', '%' . $searchQuery . '%')
            ->get();

            $shiftResults = $query->clone()
            ->where('shift_types.name', 'like', '%' . $searchQuery . '%')
            ->get();

            $userResults = $query->clone()
            ->where('users.name', 'like', '%' . $searchQuery . '%')
            ->get();

            if(is_numeric($searchQuery)){
                $salaryResults = $query->clone()
                ->where('job_offers.min_salary', '>=' , $searchQuery)
                ->get();

                $offers = array_merge($offers, $salaryResults->toArray());
            }

            $offers = array_merge($offers, $nameResults->toArray());
            $offers = array_merge($offers, $positionResults->toArray());
            $offers = array_merge($offers, $stateResults->toArray());
            $offers = array_merge($offers, $cityResults->toArray());
            $offers = array_merge($offers, $typeResults->toArray());
            $offers = array_merge($offers, $shiftResults->toArray());
            $offers = array_merge($offers, $userResults->toArray());
            
            // Get the distinct record only
            $uniquePrograms = collect($programs)->unique('program_id')->values()->all();
            $uniqueOffers = collect($offers)->unique('offer_id')->values()->all();
            
            return response()->json([
                'programs' => $uniquePrograms,
                'offers' => $uniqueOffers,
            ]);
        }
    }

    public static function getProgramsByApprovedStatus(){

        $query = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 2]
        ])
        ->select(
            'programs.name',
            'programs.start_date',
            'programs.end_date',
            'programs.program_id',
        );

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            $userID = Auth::user()->id;

            if($roleNo == 5){
                $query = $query->join('participants as p', 'p.program_id', '=', 'programs.program_id')
                ->where([
                    ['p.status', 1],
                    ['p.user_id', $userID],
                ]);
            }
            elseif($roleNo == 4){
                $query = $query->join('participants as p', 'p.program_id', '=', 'programs.program_id')
                ->where([
                    ['p.status', 1],
                    ['p.user_id', $userID],
                ])
                ->orWhere([
                    ['programs.user_id', $userID],
                ])
                ->distinct();
            }
            elseif($roleNo == 3){
                $query = $query->where([
                    ['programs.user_id', $userID],
                ]);
            }

        }

        $programs = $query->get();

        // dd($programs);

        return $programs;

    }

    // Function to return list of programs display in landings page
    public function getPrograms(Request $request){

        $events = [];
        
        // Call function from ProgramController to get all programs
        $programs = $this->getProgramsByApprovedStatus();

        if(isset($programs)){
            // Loop to filter programs
            foreach ($programs as $program) {
                $events[] = [
                    'title' => $program->name, 
                    'start' => $program->start_date,
                    'end' => $program->end_date,
                    'id' => $program->program_id,
                ];
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
