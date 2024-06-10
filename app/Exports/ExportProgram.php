<?php

namespace App\Exports;

use App\Models\Program;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Carbon\Carbon;


class ExportProgram implements FromCollection, withHeadings, ShouldAutoSize
{
    // Declare varible
    private $id;
    private $state;
    private $type;
    private $status;
    private $startDate;
    private $endDate;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function __construct($id, $state, $type, $status, $startDate, $endDate)
    {
        $this->id = $id;
        $this->state = $state;
        $this->type = $type;
        $this->status = $status;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
    }

    public function parseDate($olddate){
        try {
            // Parse the date with the specified format
            $date = Carbon::createFromFormat('Y-m-d', $olddate);

            // Set the locale to Malay
            $date->locale('ms');

            // Format the date to 'dddd, D MMMM YYYY' (without time since it's not provided)
            $formattedDate = $date->isoFormat('dddd, D MMMM YYYY');

            return $formattedDate;

        } 
        catch (Exception $e) {
            return $date;
        }
    }

    public function collection()
    {
        $query = Program::where([
            ['status', $this->status],
            ['start_date', '>=', $this->startDate],
            ['start_date', '<=', $this->endDate],
        ])
        ->with('organization');

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

                $program->start_date = $this->parseDate($program->start_date);
                $program->end_date = $this->parseDate($program->end_date);
                $program->close_date = $this->parseDate($program->close_date);
                
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
                    'Status'  => $approval,
                    'Diproses Oleh' => $program->organization->name,
                    'Diproses Pada' => $program->approved_at,
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
            'Status',
            'Diproses Oleh',
            'Diproses Pada',
        ];
    }
}
