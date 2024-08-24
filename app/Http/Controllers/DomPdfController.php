<?php

namespace App\Http\Controllers;

use PDF;
use Illuminate\Http\Request;
use App\Models\Transaction;

class DomPdfController extends Controller
{
    //

    private $paypalCurrency;

    public function __construct()
    {
        $this->paypalCurrency = \Config::get('app.PAYPAL_CURRENCY');
    }

    public function getInvoice(){

        $data = session('invoice_data');

        if(!$data){
            return redirect()->route('createTransaction')
            ->withErrors(['message' => $response['message'] ?? 'Maaf. Sumbangan tidak berjaya']);
        }

        return view('transactions.invoicePDF', compact('data'));
    }

    public function viewInvoice(Request $request){

        $refNo = $request->get('referenceNo');

        if($refNo){
            $payment = Transaction::where('reference_no', $refNo)->first();

            $data = [
                'transactionID' => $payment->reference_no,
                'payerName' => $payment->payer_name,
                'description' => $payment->references,
                'price' => number_format($payment->amount, 2),
                'currency' => $payment->currency,
                'receiptNo' => time(),
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

    public function printInvoice(Request $request){
        $data = session('invoice_data');
        $refNo = $request->get('referenceNo');

        if($refNo){

            $payment = Transaction::where('reference_no', $refNo)->first();

            $data = [
                'transactionID' => $payment->reference_no,
                'payerName' => $payment->payer_name,
                'description' => $payment->references,
                'price' => number_format($payment->amount, 2),
                'currency' => $payment->currency,
                'receiptNo' => time(),
            ];

        }
        
        if(!$data){
            return redirect('/');
        }

        $pdf = PDF::loadView('transactions.printInvoice', ['data' => $data]);
        
        return $pdf->stream('Resit Transaksi-'.time().'.pdf');
    }

}
