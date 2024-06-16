<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Carbon\Carbon;

class DateController extends Controller
{
    //
    public static function parseDate($olddate){
        try {
            // Parse the date with the specified format
            $date = Carbon::createFromFormat('Y-m-d', $olddate);

            // Set the locale to Malay
            $date->locale('ms');

            // Format the date to 'dddd, DD-MM-YYYY' (without time since it's not provided)
            $formattedDate = $date->isoFormat('dddd, DD-MM-YYYY');

            return $formattedDate;

        } 
        catch (Exception $e) {
            return $date;
        }
    }
}
