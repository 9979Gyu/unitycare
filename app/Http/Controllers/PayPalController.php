<?php

namespace App\Http\Controllers;

use App\Models\Program;
use App\Models\Program_Spec;
use App\Models\Participant;
use App\Models\Transaction;
use App\Models\User;
use Carbon\Carbon;
use DataTables;
use PDF;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportTransaction;
use App\Exports\ExportPayment;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\Mail;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;
use App\Http\Controllers\ParticipantController;

class PayPalController extends Controller{

    private $paypalCurrency;

    public function __construct()
    {
        $this->paypalCurrency = \Config::get('app.PAYPAL_CURRENCY');
    }

    public function index(){
        if(Auth::check() && Auth::user()->roleID == 1){
            $roleNo = Auth::user()->roleID;
            $paypalCurrency = $this->paypalCurrency;
            return view('donations.index', compact('roleNo', 'paypalCurrency'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to get list of transaction by the selection option
    public function retrieveTransaction($startDate, $endDate){
        $query = Transaction::where([
            ['payment_status', 1],
            ['transaction_type_id', 1],
        ]);

        if($startDate != '' && $endDate != ''){
            $query = $query->where([
                ['created_at', '>=', $startDate],
                ['created_at', '<=', $endDate],
            ]);
        }

        $selectedData = $query->select(
            'reference_no',
            'references',
            'payer_name',
            'amount',
            'created_at',
        )
        ->get();

        $selectedData->transform(function ($item) {
            $item->formatted_created_at = $item->created_at->format('d M Y H:i:s');
            $item->formatted_amount = number_format($item->amount, 2);
            $item->references = json_decode($item->references, true)['reason'];
            return $item;
        });

        return $selectedData;
            
    }

    // Function to display list of donation
    public function getTransactionDatatable(Request $request){

        if(request()->ajax()){
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');

            $selectedData = $this->retrieveTransaction($startDate, $endDate);

            if ($selectedData === null || $selectedData->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'draw' => $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                ]);
            }

            $table = Datatables::of($selectedData);

            $table->addColumn('action', function ($row) {
                $token = csrf_token();
                $btn = '<div class="justify-content-center">';
                $btn .= '<a class="printAnchor" href="#" id="' . $row->reference_no . '"><span class="btn btn-primary m-1" data-bs-toggle="modal" data-bs-target="#printModal"> Cetak </span></a>';
                $btn .= '<a class="deleteAnchor" href="#" id="' . $row->reference_no . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                $btn .= '</div>';

                return $btn;

            });

            $table->rawColumns(['action']);

            return $table->make(true);

        }

        return redirect('/');

    }

    // Function to export transaction data in Excel
    public function exportTransactions(Request $request){
        
        // Retrieve the validated data
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $roleID = $request->get('roleID');

        if($roleID == 1){
            $selectedData = $this->retrieveTransaction($startDate, $endDate);

            return Excel::download(new ExportTransaction($selectedData), 
                'Senarai Sumbangan - ' . time() . '.xlsx'
            );
        }

        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

    // Function to remove transaction info
    public function destroy(Request $request){

        $id = $request->get('payment_id');

        $update = Transaction::where('reference_no', $id)
            ->update([
                'payment_status' => 0,
            ]);

        if($update){
            return redirect('/view-transaction')->with('success', 'Data berjaya dipadam');
        }
        else{
            return redirect('/view-transaction')->withErrors(['message' => "Data tidak berjaya dipadam"]);
        }
    }

    /**
     * create transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function createTransaction(){
        $paypalCurrency = $this->paypalCurrency;
        return view('donations.create', compact('paypalCurrency'));
    }

    /**
     * process transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function processTransaction(Request $request){

        $amount = $request->get('amount');
        $paypalCurrency = $this->paypalCurrency;

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('successTransaction'),
                "cancel_url" => route('cancelTransaction'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => $paypalCurrency,
                        "value" => $amount
                    ],
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->away($links['href']);
                }
            }
            return redirect()
                ->route('createTransaction')
                ->with('error', 'Something went wrong.');
        } 
        else {
            return redirect()
                ->route('createTransaction')
                ->with('error', $response['message'] ?? 'Something went wrong.');
        }
    }

    /**
     * success transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function successTransaction(Request $request){
        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {

            $name = $response['purchase_units'][0]['shipping']['name']['full_name'];
            $email = $response['payer']['email_address'];
            $details = $response['purchase_units'][0]['payments']['captures'][0]['amount'];

            $loggedUser = Auth::user()->id ?? null;

            $references = [
                "email" => $email,
                "reason" => "Derma",
                "programID" => null,
            ];

            $payment = new Transaction([
                'reference_no' => $response['id'],
                'payer_name' => $name,
                'amount' => floatval($details['value']),
                'currency' => $details['currency_code'],
                'payment_status' => 1,
                'transaction_type_id' => 1, // donation
                'payer_id' => $loggedUser,
                'references' => json_encode($references),
            ]);

            $payment->save();

            $referenceArray = json_decode($payment->references, true);

            $data = [
                'transactionID' => $payment->reference_no,
                'payerName' => $payment->payer_name,
                'payerEmail' => $referenceArray['email'],
                'description' => $referenceArray['reason'],
                'price' => number_format($payment->amount, 2),
                'currency' => $payment->currency,
                'receiptNo' => time(),
                'createdAt' => Carbon::parse($payment->created_at)->format('d/m/y H:i:s'),
            ];

            // Store the data in the session
            session(['invoice_data' => $data]);
            
            return redirect()
                ->route('getInvoice')
                ->with('success', 'Terima Kasih');

        } 
        else {
            return redirect()
                ->route('createTransaction')
                ->withErrors(['message' => $response['message'] ?? 'Maaf. Sumbangan tidak berjaya']);
        }
    }

    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelTransaction(Request $request){
        return redirect()
            ->route('createTransaction')
            ->withErrors(['message' => $response['message'] ?? 'Transaksi telah dibatalkan']);
    }


    // participant pay to organizer
    public function userToOrganizerTransaction($data){

        $paypalCurrency = $this->paypalCurrency;
        $oid = $data['organizerID'];
        $pn = $data['programName'];

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();

        $response = $provider->createOrder([
            "intent" => "CAPTURE",
            "application_context" => [
                "return_url" => route('successUserToOrganizerTransaction', 
                    ['programData' => $data]),
                "cancel_url" => route('cancelUserToOrganizerTransaction'),
            ],
            "purchase_units" => [
                0 => [
                    "amount" => [
                        "currency_code" => $paypalCurrency,
                        "value" => $data['amount']
                    ],
                    "payee" => [
                        "email_address" => $data['organizerEmail'],
                    ]
                ]
            ]
        ]);

        if (isset($response['id']) && $response['id'] != null) {
            // redirect to approve href
            foreach ($response['links'] as $links) {
                if ($links['rel'] == 'approve') {
                    return redirect()->to($links['href'])->send();
                }
            }
        } 
        
        return redirect()
            ->route('/viewallprograms')
            ->with('error', $response['message'] ?? 'Something went wrong.');

    }

    /**
     * success transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function successUserToOrganizerTransaction(Request $request){

        $provider = new PayPalClient;
        $provider->setApiCredentials(config('paypal'));
        $paypalToken = $provider->getAccessToken();
        $response = $provider->capturePaymentOrder($request['token']);

        if (isset($response['status']) && $response['status'] == 'COMPLETED') {
            $name = $response['purchase_units'][0]['shipping']['name']['full_name'];
            $email = $response['payer']['email_address'];
            $details = $response['purchase_units'][0]['payments']['captures'][0]['amount'];
            
            $programData = $request->query('programData');
            $loggedUser = Auth::user()->id ?? null;

            $references = [
                "email" => $email,
                "reason" => "Yuran Pendaftaran-" . $programData['programName'],
                "programID" => $programData['programID'],
            ];

            $payment = new Transaction([
                'reference_no' => $response['id'],
                'payer_name' => $name,
                'amount' => floatval($details['value']),
                'currency' => $details['currency_code'],
                'payment_status' => 1,
                'transaction_type_id' => 2, // payment
                'payer_id' => $loggedUser,
                'references' => json_encode($references),
                'receiver_id' => $programData['organizerID'],
            ]);

            $payment->save();

            // Payment success then create participants
            $participant = new Participant([
                'user_type_id' => $programData['userTypeID'],
                'program_id' => $programData['programID'],
                'user_id' => $loggedUser,
                'status' => 1,
            ]);

            $participant->save();

            Program_Spec::where([
                ['program_id', $programData['programID']],
                ['user_type_id', $programData['userTypeID']],
            ])
            ->increment('qty_enrolled', 1);

            $participantController = new ParticipantController();

            $participantController->notifyUser(
                $programData['programID'], $loggedUser, 1
            );

            $referenceArray = json_decode($payment->references, true);

            $data = [
                'transactionID' => $payment->reference_no,
                'payerName' => $payment->payer_name,
                'payerEmail' => $referenceArray['email'],
                'description' => $referenceArray['reason'],
                'price' => number_format($payment->amount, 2),
                'currency' => $payment->currency,
                'receiptNo' => time(),
                'createdAt' => Carbon::parse($payment->created_at)->format('d/m/y H:i:s'),
            ];

            // Store the data in the session
            session(['invoice_data' => $data]);
            
            return redirect()
                ->route('getInvoice')
                ->with('success', 'Terima Kasih');

        } 
        
        return redirect('/viewallprograms')
            ->withErrors(['message' => $response['message'] ?? 'Maaf. Pembayaran tidak berjaya.']);

    }

    /**
     * cancel transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function cancelUserToOrganizerTransaction(Request $request){
        return redirect('/viewallprograms')
            ->withErrors(['message' => $response['message'] ?? 'Transaksi telah dibatalkan']);
    }

    // Function to display view for payment receive
    public function indexReceive(){
        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            $paypalCurrency = $this->paypalCurrency;
            $programs = Transaction::where([
                ['payment_status', 1],
                ['transaction_type_id', 2] // payments
            ])
            ->join('programs', 'programs.program_id', '=', 'transactions.references->programID');

            if(Auth::user()->roleID >= 3 && Auth::user()->roleID <= 4){
                $programs = $programs->where('receiver_id', Auth::user()->id);
            }

            $programs = $programs
            ->select('programs.name', 'programs.program_id')
            ->groupBy('programs.program_id')
            ->get();

            return view('transactions.indexReceive', compact('roleNo', 'paypalCurrency', 'programs'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to display view for payment transferred
    public function indexPayment(){
        if(Auth::check()){
            $roleNo = Auth::user()->roleID;
            $paypalCurrency = $this->paypalCurrency;
            $programs = Transaction::where([
                ['payment_status', 1],
                ['transaction_type_id', 2] // payments
            ])
            ->join('programs', 'programs.program_id', '=', 'transactions.references->programID');

            if(Auth::user()->roleID >= 3){
                $programs = $programs->where('payer_id', Auth::user()->id);
            }

            $programs = $programs
            ->select('programs.name', 'programs.program_id')
            ->groupBy('programs.program_id')
            ->get();

            return view('transactions.index', compact('roleNo', 'paypalCurrency', 'programs'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    // Function to get list of transaction by the selection option
    public function retrievePayments($startDate, $endDate, $checkPoint, $getType){

        $query = Transaction::where([
            ['payment_status', 1],
        ])
        ->join('users as payer', 'payer.id', '=', 'transactions.payer_id');

        if(Auth::user()->roleID >= 3){
            if($checkPoint == "history"){
                // payment made
                $query = $query->where('transactions.payer_id', Auth::user()->id);
            }
            elseif($checkPoint == "receive"){
                // payment received
                $query = $query->where('transactions.receiver_id', Auth::user()->id);
            }
        }

        if($startDate != '' && $endDate != ''){
            $query = $query->whereBetween('transactions.created_at', [$startDate, $endDate]);
        }

        if($getType == "donation"){
            $query = $query->where('transaction_type_id', 1);

            $selectedData = $query->select(
                'transactions.reference_no',
                'transactions.references',
                'transactions.payer_name',
                'transactions.amount',
                'transactions.created_at',
                'transactions.payment_status',
                'payer.name as account_name',
            )
            ->get();
        }
        else{
            if ($getType != "all") {
                // When getType is a program ID
                $query = $query->join('users as receiver', 'receiver.id', '=', 'transactions.receiver_id')
                    ->where([
                        ['transaction_type_id', 2],
                        ['references->programID', $getType]
                    ]);
            } 
            else {
                // When getType is "all"
                $query = $query->join('users as receiver', 'receiver.id', '=', 'transactions.receiver_id')
                    ->where('transaction_type_id', 2);
            }

            $selectedData = $query->select(
                'transactions.reference_no',
                'transactions.references',
                'transactions.payer_name',
                'transactions.amount',
                'transactions.created_at',
                'transactions.payment_status',
                'payer.name as account_name',
                'receiver.name as receiver_name'
            )
            ->get();
        }

        $selectedData->transform(function ($item) {
            $item->formatted_created_at = $item->created_at->format('d M Y H:i:s');
            $item->formatted_amount = number_format($item->amount, 2);

            $item->references = json_decode($item->references, true)['reason'];

            $item->receiver_name = $item->receiver_name ?? 'UnityCare';

            if($item->payment_status == 1){
                $item->status = "Selesai";
            }
            else{
                $item->status = "Tidak Lengkap / Dipadam";
            }
            return $item;
        });

        return $selectedData;
            
    }

    // Function to display list of payments
    public function getPaymentDatatable(Request $request){

        if(request()->ajax()){
            $startDate = $request->get('startDate');
            $endDate = $request->get('endDate');
            $checkPoint = $request->get('checkPoint');
            $getType = $request->get('type');

            $selectedData = $this->retrievePayments($startDate, $endDate, $checkPoint, $getType);

            if ($selectedData === null || $selectedData->isEmpty()) {
                return response()->json([
                    'data' => [],
                    'draw' => $request->input('draw', 1),
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                ]);
            }

            $table = Datatables::of($selectedData);

            $table->addColumn('action', function ($row) {
                $token = csrf_token();
                $btn = '<div class="justify-content-center">';
                $btn .= '<a class="printAnchor" href="#" id="' . $row->reference_no . '"><span class="btn btn-primary m-1" data-bs-toggle="modal" data-bs-target="#printModal"> Cetak </span></a>';
                if(Auth::user()->roleID == 1 || Auth::user()->roleID == 2){
                    $btn .= '<a class="deleteAnchor" href="#" id="' . $row->reference_no . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                }
                $btn .= '</div>';

                return $btn;

            });

            $table->rawColumns(['action']);

            return $table->make(true);

        }

        return redirect('/');

    }

    // Function to export transaction data in Excel
    public function exportPayments(Request $request){
        
        // Retrieve the validated data
        $startDate = $request->get('startDate');
        $endDate = $request->get('endDate');
        $checkPoint = $request->get('checkPoint');
        $getType = $request->get('type');

        $selectedData = $this->retrievePayments($startDate, $endDate, $checkPoint, $getType);

        if($checkPoint == "history"){
            return Excel::download(new ExportPayment($selectedData), 
                'Sejarah Transaksi - ' . time() . '.xlsx'
            );
        }
        else{
            return Excel::download(new ExportPayment($selectedData), 
                'Senarai Bayaran - ' . time() . '.xlsx'
            );
        }

        return redirect()->back()->withErrors(["message" => "Eksport Excel tidak berjaya"]);
        
    }

    // Function to remove transaction info
    public function destroyPayment(Request $request){

        $id = $request->get('payment_id');

        $update = Transaction::where('reference_no', $id)
            ->update([
                'payment_status' => 0,
            ]);

        if($update){
            return redirect('/view-payments')->with('success', 'Data berjaya dipadam');
        }
        else{
            return redirect('/view-payments')->withErrors(['message' => "Data tidak berjaya dipadam"]);
        }
    }
}
