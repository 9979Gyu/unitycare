<?php

namespace App\Exports;

use App\Models\Job_Offer;
use App\Models\Job;
use App\Models\Shift_Type;
use App\Models\Job_Type;
use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use DB;

class ExportOffer implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $id;
    private $state;
    private $status;
    private $startDate;
    private $endDate;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id, $state, $status, $startDate, $endDate){
        $this->id = $id;
        $this->state = $state;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        if(isset($this->id) && isset($this->state) && isset($this->status)){
            $query = Job_Offer::where([
                ['job_offers.status', $this->status],
                ['job_offers.created_at', '>=', $this->startDate],
                ['job_offers.created_at', '<=', $this->endDate],
            ]);

            if ($this->state != 3) {
                $query->where('job_offers.approval_status', $this->state);
            }

            if ($this->state == 0 || $this->state == 2) {
                $query->leftJoin('users as p', 'p.id', '=', 'job_offers.approved_by');
            }

            $selectedOffers = $query
                ->join('users as u', 'u.id', '=', 'job_offers.user_id')
                ->join('job_types as jt', 'jt.job_type_id', '=', 'job_offers.job_type_id')
                ->join('shift_types as st', 'st.shift_type_id', '=', 'job_offers.shift_type_id')
                ->join('jobs as j', 'j.job_id', '=', 'job_offers.job_id')
                ->select(
                    'job_offers.*',
                    'u.name as username', 
                    'u.contactNo as usercontact', 
                    'u.email as useremail',
                    'j.name as jobname',
                    'j.position as jobposition',
                    'jt.name as typename',
                    'st.name as shiftname',
                    DB::raw("DATE(job_offers.updated_at) as updateDate"),
                    'job_offers.description->description as description',
                    'job_offers.description->reason as reason',
                );

            // Add the approval name to the select statement conditionally
            if ($this->state == 0 || $this->state == 2) {
                $selectedOffers->addSelect('p.name as approvalname');
            } 
            else {
                // Ensure that approvalname is always selected if approved_by is not null
                $selectedOffers->leftJoin('users as p', 'p.id', '=', 'job_offers.approved_by')
                ->addSelect('p.name as approvalname');
            }

            $selectedOffers = $selectedOffers->orderBy('job_offers.updated_at', 'desc')->get(); 

            if(isset($selectedOffers)){
                $selectedOffers = $selectedOffers->map(function ($item) {

                    if($item->approval_status == 0){
                        $approval = "Ditolak";
                    }
                    elseif($item->approval_status == 1){
                        $approval = "Belum Diproses";
                    }
                    else{
                        $approval = "Telah Diluluskan";
                    }

                    return[
                        'Nama' => $item->jobname,
                        'Jawatan' => $item->jobposition,
                        'Tempat' => $item->city . ", " . $item->state,
                        'Jenis' => $item->typename,
                        'Syif' => $item->shiftname,
                        'Purata Gaji' => "RM " . $item->min_salary . " - RM " . $item->max_salary,
                        'Penerangan' => $item->description,
                        'Nama Pengurus' => $item->username,
                        'Emel Pengurus' => $item->useremail,
                        'Telefon Nombor Pengurus' => $item->usercontact,
                        'Status' => $approval,
                        'Diproses Oleh' => $item->approvalname,
                        'Diproses Pada' => $item->approved_at,
                    ];
                });
            }
    
            return $selectedOffers;
        }

    }

    public function headings(): array{
        return [
            'Nama',
            'Jawatan',
            'Tempat',
            'Jenis',
            'Syif',
            'Purata Gaji',
            'Penerangan',
            'Nama Pengurus',
            'Emel Pengurus',
            'Telefon Nombor Pengurus',
            'Status',
            'Diproses Oleh',
            'Diproses Pada',
        ];
    }
}
