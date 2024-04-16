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
}
