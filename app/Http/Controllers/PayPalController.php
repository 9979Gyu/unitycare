<?php

namespace App\Http\Controllers;

use App\Models\Payment;
use DataTables;
use Illuminate\Support\Facades\Auth;
use App\Exports\ExportTransaction;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Srmklive\PayPal\Services\PayPal as PayPalClient;

class PayPalController extends Controller{

    public function index(){
        if(Auth::check() && Auth::user()->roleID == 1){
            $roleNo = Auth::user()->roleID;
            $paypalCurrency = 'SGD';
            return view('donations.index', compact('roleNo', 'paypalCurrency'));
        }
        else{
            return redirect('/')->withErrors(['message' => 'Anda tidak dibenarkan untuk melayari halaman ini']);
        }
    }

    public function retrieveTransaction($startDate, $endDate){
        $query = Payment::where('payment_status', 1);

        if($startDate != '' && $endDate != ''){
            $query = $query->where([
                ['created_at', '>=', $startDate],
                ['created_at', '<=', $endDate],
            ]);
        }

        $selectedData = $query->select(
            'id',
            'transaction_id',
            'payer_name',
            'amount',
            'created_at',
        )
        ->get();

        $selectedData->transform(function ($item) {
            $item->formatted_created_at = $item->created_at->format('d M Y H:i:s');
            $item->formatted_amount = number_format($item->amount, 2);
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
                $btn .= '<a class="deleteAnchor" href="#" id="' . $row->id . '"><span class="btn btn-danger m-1" data-bs-toggle="modal" data-bs-target="#deleteModal"> Padam </span></a>';
                $btn .= '</div>';

                return $btn;

            });

            $table->rawColumns(['action']);

            return $table->make(true);

        }

        return redirect('/');

    }

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

    public function destroy(Request $request){

        $id = $request->get('payment_id');
        $update = Payment::where('id', $id)
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
        return view('donations.create');
    }

    /**
     * process transaction.
     *
     * @return \Illuminate\Http\Response
     */
    public function processTransaction(Request $request){

        $amount = $request->get('amount');

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
                        "currency_code" => "SGD",
                        "value" => $amount
                    ]
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
            $details = $response['purchase_units'][0]['payments']['captures'][0]['amount'];

            $payment = new Payment([
                'transaction_id' => $response['id'],
                'payer_name' => $name,
                'amount' => floatval($details['value']),
                'currency' => $details['currency_code'],
                'payment_status' => 1,
            ]);

            $payment->save();

            return redirect()
                ->route('createTransaction')
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
}
