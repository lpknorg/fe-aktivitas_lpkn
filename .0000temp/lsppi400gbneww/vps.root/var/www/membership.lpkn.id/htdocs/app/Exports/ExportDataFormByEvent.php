<?php

namespace App\Exports;

use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\{
    FromView,
    WithColumnWidths,
    WithEvents
};
use Maatwebsite\Excel\Events\AfterSheet;

class ExportDataFormByEvent implements FromView, WithColumnWidths,WithEvents
{
    private $alData;
    public function __construct($data){
        $this->alData = $data;
    }

    public function columnWidths(): array
    {
        return [
            'A' => 5,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 35,
            'F' => 35,
            'G' => 20,
            'H' => 30,
            'I' => 20,
            'J' => 30,
            'K' => 20,
            'L' => 25,
            'M' => 25,
            'N' => 23,
            'O' => 35,
            'P' => 20,
            'Q' => 45,
            'R' => 20,
            'S' => 23,
            'T' => 20,
            'U' => 70,
            'V' => 60,
            'W' => 70,
            'X' => 10,
            'Y' => 50,
            'Z' => 20,
            'AA' => 20,
            'AB' => 20,
            'AC' => 28,
            'AD' => 20,
            'AE' => 15
        ];
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class    => function(AfterSheet $event) {
                $event->sheet->getDelegate()->getStyle('A1:AE1')
                ->getFill()
                ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                ->getStartColor()
                ->setARGB('f06e5d');

                $event->sheet->getDelegate()->getStyle('A1:AE1')
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);


                $keyy = 1;
                foreach($this->alData as $data){
                    $keyy++;
                    $bgColor = $data->bg_color ? str_replace("#","",$data->bg_color) : '#fff';
                    $event->sheet->getDelegate()->getStyle("A{$keyy}:AE{$keyy}")
                    ->getFill()
                    ->setFillType(\PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID)
                    ->getStartColor()
                    ->setARGB($bgColor);

                    $event->sheet->getDelegate()->getStyle("A{$keyy}:AE{$keyy}")
                    ->getBorders()->getAllBorders()->setBorderStyle(\PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN);
                }
            },
        ];
    }

    public function view(): View
    {
        $data = $this->alData;
        return view('data_form_event', compact('data'));
    }
}