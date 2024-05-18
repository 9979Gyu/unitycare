<?php

namespace App\Http\Controllers;

use App\Models\Job;
use App\Models\Job_Offer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use DataTables;
use Illuminate\Support\Facades\Auth;

class OfferController extends Controller
{
    // Function to display list of job offered
    public function index(){

        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('job_offers.index', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }

    }

    // Function to display add offer form
    public function create(){
        
        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            return view('job_offers.add', compact('roleNo'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }

    }

    // Function to store offer into job_offers table in database
    public function store(){

    }

    // Function to display edit offer form
    public function edit(){

    }

    // Function to update edited offer
    public function update(){

    }

    // Function to remove offer from database
    public function destroy(){

    }
}
