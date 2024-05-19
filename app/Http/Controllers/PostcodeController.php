<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

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

    public function getCityState(){
        $data = File::get(database_path('postcode.json'));
        $decodedData = json_decode($data, true);

        $citiesstates = [];

        foreach ($decodedData['state'] as $state) {
            // Get the name of the state
            $citiesstates[] = $state['name'];

            // Get the names of cities within each state
            foreach ($state['city'] as $city) {
                $citiesstates[] = $city['name'];
            }
        }

        $uniqueItem = array_unique($citiesstates);

        return $uniqueItem;
    }
}
