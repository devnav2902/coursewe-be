<?php

namespace App\Exports;

use App\Http\Controllers\HelperController;
use App\Http\Controllers\OverviewController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Date;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithMapping;
use PhpOffice\PhpSpreadsheet\Style\NumberFormat;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithStrictNullComparison;
use Maatwebsite\Excel\Concerns\Exportable;
use Illuminate\Contracts\Support\Responsable;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithColumnFormatting;


class RevenueExport implements FromArray, WithMapping, WithHeadings, WithStyles, WithStrictNullComparison, Responsable, ShouldAutoSize, WithColumnFormatting
{
    use Exportable;

    private $month;
    private $year;
    private $currentMonth;
    private $currentYear;
    private $fromDate;
    private $toDate;
    private $fileName;
    private $revenueData;
    private $helper;

    function __construct($request)
    {
        $controller = new OverviewController();
        $helper = new HelperController();

        $this->helper = $helper;

        $revenueData = $controller->getRevenue($request)->getData()->revenueData;

        if ($request->has('LTM')) {
            $month = $controller->month;
            $currentMonth = $controller->currentMonth;
            $year = $controller->year;
            $currentYear = $controller->currentYear;

            $fileName =  $month . '-' . $year . '_' . $currentMonth . '-' . $currentYear . '-DOANH_THU.xlsx';

            $this->fileName = $fileName;
            $this->year = $year;
            $this->month = $month;
            $this->currentMonth = $currentMonth;
            $this->currentYear = $currentYear;
        }

        if ($request->has('fromDate') && $request->has('toDate')) {
            $fromDate = $this->helper->formatDate($request->input('fromDate'), 'd-m-Y');
            $toDate = $this->helper->formatDate($request->input('toDate'), 'd-m-Y');
            $fileName =  $fromDate . '_' . $toDate . '-DOANH_THU.xlsx';

            $this->fileName = $fileName;
            $this->fromDate = $fromDate;
            $this->toDate = $toDate;
        }

        $this->revenueData = $revenueData;
    }


    public function styles(Worksheet $sheet)
    {
        return [
            // Style the first row as bold text.
            1 => ['font' => ['bold' => true, 'size' => 18]],
            4 => ['font' => ['bold' => true]],
        ];
    }

    public function headings(): array
    {
        $rowInfo = empty($this->month) ?
            ['Từ ngày ' . $this->fromDate . ' Đến ngày ' . $this->toDate] :
            [
                'Từ tháng ' . $this->month . '-' . $this->year .
                    ' Đến tháng ' . $this->currentMonth . '-' . $this->currentYear
            ];

        return [
            [
                'BÁO CÁO DOANH THU',
            ],
            $rowInfo,
            [],
            [
                empty($this->month) ?  'Ngày' : 'Tháng',
                'Doanh thu',
            ]
        ];
    }

    public function array(): array
    {
        return $this->revenueData;
    }

    public function map($revenue): array
    {
        return [
            empty($this->month) ?
                $this->helper->formatDate($revenue->date, 'd-m-Y') :
                $this->helper->formatDate($revenue->date, 'm-Y'),
            $revenue->revenue,
        ];
    }

    public function columnFormats(): array
    {
        return [
            'A' => NumberFormat::FORMAT_DATE_DDMMYYYY,
            'B' => NumberFormat::FORMAT_NUMBER_COMMA_SEPARATED1,
        ];
    }
}
