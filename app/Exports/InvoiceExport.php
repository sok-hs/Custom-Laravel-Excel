<?php

namespace App\Exports;

use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class InvoiceExport implements WithMultipleSheets
{

    use Exportable;

    private $systemName;
    protected $year;

    public function __construct (string $systemName)
    {
        $this->systemName = $systemName;
    }

    public function sheets(): array
    {
        $sheets = [];
        $arrSystemName = explode(",", $this->systemName);

        foreach ($arrSystemName as $temp)
        {
            $sheets[] = new InvoicePerMonthSheet($temp);
        }

        return $sheets;
    }




}
