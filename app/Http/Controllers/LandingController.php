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
        
        $now =  date('Y-m-d');
        // Check if user logged in and is B40/OKU
        if(Auth::check() && Auth::user()->roleID == 5){

            // Get logged in user's poor id
            $poorId = Auth::user()->poor->poor_id;

            // Retrieve sectors based on approved application which the user has made
            $sectors = Sector::whereHas('organizations.jobOffers.applications', function ($query) use ($poorId) {
                $query->where([
                    ['approval_status', 2],
                    ['status', 1],
                    ['poor_id', $poorId],
                ]);
            })
            ->with([
                'organizations.jobOffers' => function ($query) use ($poorId, $now) {
                    $query->whereHas('applications', function ($query) use ($poorId, $now) {
                        $query->where([
                            ['approval_status', 2],
                            ['status', 1],
                            ['poor_id', $poorId]
                        ])
                        ->where(function ($query) use ($now) {
                            $query->whereNull('end_date')
                                  ->orWhere('end_date', '>=', $now);
                        });
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
                    ['is_full', 0],
                ]);
            })
            ->with([
                'organizations.jobOffers' => function ($query) use($now){
                    $query->with([
                        'shiftType',
                        'jobType',
                        'organization'
                    ])
                    ->where([
                        ['approval_status', 2],
                        ['status', 1],
                        ['is_full', 0],
                    ])
                    ->where(function ($query) use ($now) {
                        $query->whereNull('end_date')
                              ->orWhere('end_date', '>=', $now);
                    });
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
        $now =  date('Y-m-d');

        if(isset($searchQuery)){

            $programQuery = DB::table('programs')
                ->join('users', 'users.id', '=', 'programs.user_id')
                ->where([
                    ['programs.status', 1],
                    ['programs.approved_status', 2],
                    ['programs.end_date', '>=', $now],
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
                ['job_offers.end_date', '>=', $now],
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

        $now =  date('Y-m-d');

        $query = Program::where([
            ['programs.status', 1],
            ['programs.approved_status', 2],
            ['programs.end_date', '>=', $now],
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

    // function to display number of user by role
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
