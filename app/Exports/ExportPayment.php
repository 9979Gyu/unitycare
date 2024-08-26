<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportPayment implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $selectedData;
    /**
    * @return \Illuminate\Support\Collection
    */

    public function __construct($selectedData)
    {
        $this->selectedData = $selectedData;
    }

    public function collection()
    {
        //
        if(isset($this->selectedData)){

            $selectedData = $this->selectedData->map(function ($item) {

                return[
                    'No Rujukan' => $item->reference_no,
                    'Nama Pembayar' => $item->payer_name . ' (' . $item->account_name . ')',
                    'Tujuan' => $item->references,
                    'Nilai' => $item->formatted_amount,
                    'Tarikh' => $item->formatted_created_at,
                    'Nama Penerima' => $item->receiver_name,
                ];
            });
    
            return $selectedData;
        }
    }

    public function headings(): array{
        return [
            'No Rujukan',
            'Nama Pembayar',
            'Tujuan',
            'Nilai (' . \Config::get('app.PAYPAL_CURRENCY') . ')',
            'Tarikh',
            'Nama Penerima'
        ];
    }
}
