<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use thiagoalessio\TesseractOCR\TesseractOCR;

class OCRController extends Controller
{
    //
    public function extractText(Request $request){

        $file = $request->file('image');
        
        $imagePath = $file->store('images', 'public');

        $fullPath = storage_path('app/public/' . $imagePath);

        // Create a new instance of TesseractOCR
        $tesseract = new TesseractOCR($fullPath);

        // Set the language of the text in the image
        $tesseract->lang('eng');

        // Get the text from the image
        $text = $tesseract->run();

        // Remove the '-' on IC number
        $text = str_replace('-', '', $text);

        dd($text);

    }

    

}
