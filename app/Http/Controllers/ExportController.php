<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Exports\InvoiceExport;
use Illuminate\Support\Facades\DB;

class ExportController extends Controller
{
    public function export1 ()
    {
        $systemName = "";
        /// get systems
        $systems = DB::connection("mysql")->select("SELECT system_name, slug, db_name, tbl_user_name, tbl_role_name, tbl_branch_name FROM system_info WHERE status = 1;");

        foreach ($systems as $sysInfo)
        {
            /// connection
            config(["database.connections.mysql_ddm.host" => "localhost"]);
            config(["database.connections.mysql_ddm.port" => "3306"]);
            config(["database.connections.mysql_ddm.database" => $sysInfo->db_name]);
            config(["database.connections.mysql_ddm.username" => "root"]);
            config(["database.connections.mysql_ddm.password" => ""]);

            $data = DB::connection("mysql_ddm")->select("select * from " . $sysInfo->db_name . "." . $sysInfo->tbl_user_name . ";");

            $systemName .= $sysInfo->system_name . ",";

        }

        return (new InvoiceExport($systemName))->download('invoices.xlsx');


    }

    public function export2 ()
    {
        


    }

    public function export3 ()
    {
        


    }
}
