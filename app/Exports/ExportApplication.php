<?php

namespace App\Exports;

use App\Models\Job;
use App\Models\Application;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportApplication implements FromCollection, WithHeadings, ShouldAutoSize
{
    private $id;
    private $state;
    private $status;
    private $selectedPosition;
    private $userID;
    private $startDate;
    private $endDate;
    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id, $state, $status, $selectedPosition, $userID, $startDate, $endDate)
    {
        $this->id = $id;
        $this->state = $state;
        $this->status = $status; 
        $this->selectedPosition = $selectedPosition;
        $this->userID = $userID;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function collection()
    {
        if(isset($this->selectedPosition)){

            $query = Application::where([
                ['applications.status', $this->status],
                
            ]);

            if ($this->state != 3) {
                $query->where('applications.approval_status', $this->state);
            }

            if ($this->state == 0 || $this->state == 2) {
                $query->leftJoin('users as p', 'p.id', '=', 'applications.approved_by');
            }

            $selectedApplication = $query
                ->join('poors', 'poors.poor_id', '=', 'applications.poor_id')
                ->join('users as u', 'u.id', '=', 'poors.user_id')
                ->join('job_offers as jo', 'jo.offer_id', '=', 'applications.offer_id')
                ->join('jobs as j', 'j.job_id', '=', 'jo.job_id')
                ->join('disability_types as dt', 'dt.dis_type_id', '=', 'poors.disability_type')
                ->join('education_levels as el', 'el.edu_level_id', '=', 'poors.education_level')
                ->where([
                    ['jo.status', 1],
                    ['j.status', 1],
                    ['jo.user_id', $this->userID],
                    ['j.job_id', $this->selectedPosition],
                ])
                ->select(
                    'applications.*',
                    'applications.description->description as description',
                    'applications.description->reason as reason',
                    'u.name as username',
                    'u.email as useremail',
                    'u.contactNo as usercontact',
                    'poors.disability_type',
                    'dt.name as category',
                    'el.name as edu_level',
                    'j.position as position'
                );

            // Add the approval name to the select statement conditionally
            if ($this->state == 0 || $this->state == 2) {
                $selectedApplication->addSelect('p.name as approvalname');
            } 
            else {
                // Ensure that approvalname is always selected if approved_by is not null
                $selectedApplication->leftJoin('users as p', 'p.id', '=', 'applications.approved_by')
                ->addSelect('p.name as approvalname');
            }

            $selectedApplication = $selectedApplication->orderBy("applications.applied_date", "asc")->get(); 

            if(isset($selectedApplication)){
                $selectedApplication = $selectedApplication->map(function ($item) {

                    $approvalStatus = [
                        0 => "Ditolak",
                        1 => "Belum Diproses",
                        2 => "Telah Diluluskan",
                    ];

                    return[
                        'Nama Pemohon' => $item->username,
                        'Emel Pemohon' => $item->useremail,
                        'Telefon Nombor Pemohon' => $item->usercontact,
                        'Peringkat Pendidikan' => $item->edu_level,
                        'Kategori' => $item->disability_type,
                        'Sebab Mohon' => $item->description,
                        'Tarikh Mohon' => $item->created_at, 
                        'Jawatan' => $item->position, 
                        'Status' => $approvalStatus[$item->approval_status] ?? 'Tidak diketahui',
                        'Diproses Oleh' => $item->approvalname, 
                        'Diproses Pada' => $item->approved_at, 
                    ];
                });

            }
            return collect($selectedApplication);
            
        }

    }

    public function headings(): array
    {
        return [
            'Nama Pemohon',
            'Emel Pemohon',
            'Telefon Nombor Pemohon',
            'Peringkat Pendidikan',
            'Kategori',
            'Sebab Mohon',
            'Tarikh Mohon',
            'Jawatan',
            'Status',
            'Diproses Oleh',
            'Diproses Pada',
        ];
    }
}
