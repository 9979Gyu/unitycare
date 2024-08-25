<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportTransaction implements FromCollection, WithHeadings, ShouldAutoSize
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
                    'Nama' => $item->payer_name,
                    'Tujuan' => $item->references,
                    'Nilai' => $item->formatted_amount,
                    'Tarikh' => $item->formatted_created_at,
                ];
            });
    
            return $selectedData;
        }
    }

    public function headings(): array{
        return [
            'No Rujukan',
            'Nama',
            'Tujuan',
            'Nilai (' . \Config::get('app.PAYPAL_CURRENCY') . ')',
            'Tarikh'
        ];
    }
}
