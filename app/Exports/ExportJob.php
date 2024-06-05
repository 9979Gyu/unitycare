<?php

namespace App\Exports;

use App\Models\Job;
use App\Models\Shift_Type;
use App\Models\Job_Type;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportJob implements FromCollection, WithHeadings, ShouldAutoSize
{

    private $id;
    private $type;
     
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id, $type)
    {
        $this->id = $id;
        $this->type = $type;
    }

    public function collection()
    {
        if(isset($this->id) && isset($this->type)){

            if($this->type == "job"){
                $selectedItem = Job::select(
                    'job_id as id',
                    DB::raw('CONCAT(name, " - ", position) as name'),
                    'description'
                )
                ->withCount('jobOffers')
                ->where('status', 1)
                ->groupBy('job_id', 'name', 'description', 'position')
                ->orderBy('name')
                ->get();

            }
            else if($this->type == "shift"){
                $selectedItem = Shift_Type::select(
                    'shift_type_id as id',
                    'name',
                    'description',
                )
                ->withCount('jobOffers')
                ->where('status', 1)
                ->groupBy('shift_type_id', 'name', 'description')
                ->orderBy('name')
                ->get();
            }
            else{
                $selectedItem = Job_Type::select(
                    'job_type_id as id',
                    'name',
                    'description',
                )
                ->withCount('jobOffers')
                ->where('status', 1)
                ->groupBy('job_type_id', 'name', 'description')
                ->orderBy('name')
                ->get();
            }
            
            if(isset($selectedItem)){
                $selectedItem = $selectedItem->map(function ($item) {

                    if($item->job_offers_count > 0){
                        $count = $item->job_offers_count;
                    }
                    else{
                        $count = "0";
                    }

                    return[
                        'Nama' => $item->name,
                        'Penerangan' => $item->description,
                        'Bilangan Pengguna' => $count
                    ];
                });
            }
    
            return $selectedItem;

        }
    }

    public function headings(): array
    {
        return [
            'Nama',
            'Penerangan',
            'Bilangan Pengguna',
        ];
    }
}
