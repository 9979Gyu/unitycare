<?php

namespace App\Http\Controllers;

use Carbon\Carbon;
use DB;
use PDF;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use App\Mail\InvoiceEmail;
use App\Models\Participant;
use App\Models\Program;
use App\Models\Transaction;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class DomPdfController extends Controller
{
    //

    private $paypalCurrency;

    public function __construct()
    {
        $this->paypalCurrency = \Config::get('app.PAYPAL_CURRENCY');
    }

    // function to display receipt for view
    public function getInvoice(){

        $data = session('invoice_data');

        if(!$data){
            return redirect()->route('createTransaction')
            ->withErrors(['message' => $response['message'] ?? 'Maaf. Sumbangan tidak berjaya']);
        }

        // Send the email with the Receipt attachment
        Mail::to($data['payerEmail'])->send(new InvoiceEmail($data));

        return view('transactions.invoicePDF', compact('data'));
    }

    // function to display receipt for download in pdf
    public function printInvoice(Request $request){

        $data = session('invoice_data');
        $refNo = $request->get('referenceNo');

        if($refNo){

            $payment = Transaction::where('reference_no', $refNo)->first();

            $newReference = explode('|', $payment->references);

            $data = [
                'transactionID' => $payment->reference_no,
                'payerName' => $payment->payer_name,
                'payerEmail' => $newReference[0],
                'description' => $newReference[1],
                'price' => number_format($payment->amount, 2),
                'currency' => $payment->currency,
                'receiptNo' => time(),
                'createdAt' => Carbon::parse($payment->created_at)->format('d/m/y H:i:s'),
            ];
        }
        
        if(!$data){
            return redirect('/');
        }

        // Store the data in the session
        session(['invoice_data' => $data]);

        $pdf = PDF::loadView('transactions.printInvoice', ['data' => $data]);
        
        return $pdf->stream('Resit Transaksi-'.time().'.pdf');
    }

    // function to print program certificate
    public function printCert(Request $request){

        $partID = $request->get('participantID');

        if(!$partID){
            if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                return redirect('/indexparticipant')->withErrors(["message" => "Muat turun sijil tidak berjaya"]);
            }
            return redirect('/indexparticipated')->withErrors(["message" => "Muat turun sijil tidak berjaya"]);
        }

        $data = Participant::where([
            ['participants.participant_id', $partID],
            ['participants.status', 1],
        ])
        ->join('programs as p', 'p.program_id', '=', 'participants.program_id')
        ->join('users as u', 'u.id', '=', 'participants.user_id')
        ->select(
            'participants.participant_id',
            'u.name as userName',
            'p.name as programName',
            'p.name as programName',
            DB::raw('DATE_FORMAT(p.end_date, "%d/%m/%Y") as formatted_date'),
        )
        ->first();

        $pdf = PDF::loadView('participants.cert', ['data' => $data]);
        
        return $pdf->stream('Sijil Penyertaan-'.time().'.pdf');
    }


}
