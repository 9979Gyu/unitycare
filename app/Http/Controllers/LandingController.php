<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Job;
use App\Models\Job_Offer;

class LandingController extends Controller
{
    // Function to return list of programs and jobs to display in landings page
    public function getProgramsAndJobs(){

        // Call function from ProgramController to get all programs
        $programs = ProgramController::getProgramsByApprovedStatus();

        $offers = OfferController::getUpdatedOffers();

        $events = [];

        if(isset($programs) && isset($offers)){
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

            // Return events data as JSON
            return response()->json($events);

            // // Loop to filter job offers
            // foreach ($offers as $offer) {
            //     // Offer is approved by staff
            //     if($offer->approval_status == 2){
            //         $events[] = [
            //             'title' => $offer->jobname, 
            //             'start' => $offer->start_date,
            //         ];
            //     }
            // }
        }
        
    }
}
