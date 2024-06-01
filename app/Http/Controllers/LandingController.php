<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Program;
use App\Models\Job;
use App\Models\Job_Offer;
use App\Models\Sector;

class LandingController extends Controller
{
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

    // Function to return list of jobs to display in landings page
    public function getJobs(Request $request){

        $events = [];
        $offers = OfferController::getApprovedOffers();

        if(isset($offers)){

            // Loop to filter job offers
            foreach ($offers as $offer) {
                // Offer is approved by staff
                if($offer->approval_status == 2){
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
        
        // Return events data as JSON
        return response()->json(['events' => $events]);
    }
}
