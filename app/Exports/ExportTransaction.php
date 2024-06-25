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
                    'ID Transaksi' => $item->transaction_id,
                    'Nama' => $item->payer_name,
                    'Nilai' => $item->formatted_amount,
                    'Tarikh' => $item->formatted_created_at,
                ];
            });
    
            return $selectedData;
        }
    }

    public function headings(): array{
        return [
            'ID Transaksi',
            'Nama',
            'Nilai (SGD)',
            'Tarikh'
        ];
    }
}
