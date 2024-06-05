<?php

namespace App\Exports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ExportProgram implements FromCollection, withHeadings, ShouldAutoSize
{
    // Declare varible
    private $id;
    private $state;
    private $type;
    private $status;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id, $state, $type, $status)
    {
        $this->id = $id;
        $this->state = $state;
        $this->type = $type;
        $this->status = $status;
    }

    public function collection()
    {
        $query = Program::where('status', $this->status)->with('organization');

        if($this->state != 3) {
            $query->where('approved_status', $this->state);
        }

        if($this->type != 3) {
            $query->where('type_id', $this->type);
        }

        $selectedPrograms = $query->orderBy('updated_at', 'desc')->get();

        if(isset($selectedPrograms)){
            $selectedPrograms = $selectedPrograms->map(function ($program) {
                if($program->approved_status == 0){
                    $approval = "Ditolak";
                }
                elseif($program->approved_status == 1){
                    $approval = "Belum Diproses";
                }
                else{
                    $approval = "Telah Diluluskan";
                }
                return[
                    'Nama' => $program->name,
                    'Lokasi' => $program->venue,
                    'Mula' => $program->start_date . " " . $program->start_time,
                    'Tamat' => $program->end_date . " " . $program->end_time,
                    'Penerangan' => json_decode($program->description, true)['desc'] ?? '',
                    'Name Pengurus' => $program->organization->name ?? '',
                    'Emel Pengurus' => $program->organization->email ?? '',
                    'Nombor Telefon Pengurus' => $program->organization->contactNo ?? '',
                    'Tarikh Tutup Permohonan' => $program->close_date,
                    'Kategori' => ($program->type_id == 1) ? "Sukalerawan" : "Pembangunan Kemahiran" ,
                    'Diproses Oleh' => $program->organization->name,
                    'Diproses Pada' => $program->approved_at,
                    'Status'  => $approval
                ];
            });
        }

        return $selectedPrograms;

    }

    public function headings(): array
    {
        return [
            'Nama',
            'Lokasi',
            'Mula',
            'Tamat',
            'Penerangan',
            'Name Pengurus',
            'Emel Pengurus',
            'Nombor Telefon Pengurus',
            'Tarikh Tutup Permohonan',
            'Kategori',
            'Diproses Oleh',
            'Diproses Pada',
            'Status'
        ];
    }
}
