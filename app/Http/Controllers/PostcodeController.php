<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Job_Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;

class PostcodeController extends Controller
{
    //
    public function search(Request $request)
    {
        $postcode = $request->input('postcode');

        $data = File::get(database_path('postcode.json'));
        $states = json_decode($data, true)['state'];

        $result = [];

        foreach($states as $state){
            foreach($state['city'] as $city){
                if(in_array($postcode, $city['postcode'])){
                    $result[] = [
                        'state' => $state['name'],
                        'city' => $city['name']
                    ];
                }
            }
        }

        return response()->json($result);
    }

    public function getCityState(Request $request){
        $type = $request->get('type');

        if($type == "program"){
            $cities = Program::where([
                ['status', 1],
                ['approved_status', 2],
            ])
            ->select('city as location')->distinct();

            // Retrieve distinct states
            $states = Program::where([
                ['status', 1],
                ['approved_status', 2],
            ])
            ->select('state as location')->distinct();

            // Combine the results
            $citiesstates = $cities->union($states)->orderBy('location', 'asc')->get();
            
        }
        elseif($type == "offer"){
            // Retrieve distinct cities
            $cities = Job_Offer::where([
                ['status', 1],
                ['approval_status', 2],
            ])
            ->select('city as location')->distinct();

            // Retrieve distinct states
            $states = Job_Offer::where([
                ['status', 1],
                ['approval_status', 2],
            ])
            ->select('state as location')->distinct();

            // Combine the results
            $citiesstates = $cities->union($states)->orderBy('location', 'asc')->get();
            
        }

        return $citiesstates;
    }
}
