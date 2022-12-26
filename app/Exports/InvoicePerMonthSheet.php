<?php

namespace App\Exports;

use App\User;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromQuery;           # FromQuery
use Maatwebsite\Excel\Concerns\WithTitle;           # WithTitle
use Maatwebsite\Excel\Concerns\WithHeadings;        # WithHeadings
use Maatwebsite\Excel\Concerns\FromCollection;      # FromCollection
use Maatwebsite\Excel\Concerns\ShouldAutoSize;      # ShouldAutoSize
use Maatwebsite\Excel\Concerns\WithEvents;          # WithEvents


class InvoicePerMonthSheet implements WithTitle ,FromCollection ,WithHeadings ,ShouldAutoSize
{

    private $systemName;

    

    public function __construct (string $systemName)
    {
        $this->systemName = $systemName;
    }

    // public function query()
    // {
    //     // $data[] = [
    //     //     "a" => "a",
    //     //     "b" => "b",
    //     //     "c" => "c"
    //     // ];

    //     $data = collect();
    //     /// get systems
    //     $systems = DB::connection("mysql")->select("select system_name, slug, db_name, tbl_user_name, tbl_role_name, tbl_branch_name from system_info where status = 1 and type_system = 'new' limit 3;");

    //     foreach ($systems as $sysInfo)
    //     {
            
    //         if ($sysInfo->system_name === $this->systemName)
    //         {
    //             // dd(123);
    //             /// connection
    //             config(["database.connections.mysql_ddm.host" => "localhost"]);
    //             config(["database.connections.mysql_ddm.port" => "3306"]);
    //             config(["database.connections.mysql_ddm.database" => $sysInfo->db_name]);
    //             config(["database.connections.mysql_ddm.username" => "root"]);
    //             config(["database.connections.mysql_ddm.password" => ""]);

    //             $tempData = DB::connection("mysql_ddm")->select($this->default__select_user . $sysInfo->db_name . "." . $sysInfo->tbl_user_name . ";");
                
    //             foreach ($tempData as $temp)
    //             {
    //                 // $data[] = [
    //                 //     "name" => $temp->name,
    //                 //     "fullname" => $temp->fullname,
    //                 //     "email" => $temp->email,
    //                 //     "status" => $temp->status,
    //                 //     "created_at" => date("d/m/Y", strtotime($temp->created_at)),
    //                 //     "updated_at" => date("d/m/Y", strtotime($temp->created_at)),
    //                 // ];

    //                 $data->push(["name" => $temp->name, "fullname" => $temp->fullname, "email" => $temp->email]);
    //             }
    //             break;
    //         }
    //     }

    //     dd($data);

    //     $data->all();
    //     // dd(gettype($data));

    //     // $data[] = [
    //     //     "a" => "a",
    //     //     "b" => "b",
    //     //     "c" => "c"
    //     // ];

    //     return $data;
    // }

    public function title(): string
    {
        return $this->systemName . " ";
    }

    public function headings(): array
    {
        return [
            "No","Name", "Fullname", "Email", "Status", "Role", "Branch", "Created By", "Updated By", "Created At", "Updated At"
        ];
    }

    public function collection ()
    {
        
        $data = collect();
        /// get systems
        $sysInfo = DB::connection("mysql")->select("select system_name, slug, db_name, tbl_user_name, tbl_role_name, tbl_branch_name from system_info where status = 1 and system_name = '" . $this->systemName . "' limit 1;")[0] ?? null;
        
        if (($sysInfo->system_name ?? null) === $this->systemName)
        {
            $tempData = "";
            /// connection
            config(["database.connections.mysql_ddm.host" => "localhost"]);
            config(["database.connections.mysql_ddm.port" => "3306"]);
            config(["database.connections.mysql_ddm.database" => $sysInfo->db_name]);
            config(["database.connections.mysql_ddm.username" => "root"]);
            config(["database.connections.mysql_ddm.password" => ""]);

            /// get data
            if ($sysInfo->slug == "it_asset_management" )
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    SELECT u.username 
                        ,CONCAT(u.first_name, ' ', u.last_name) AS fullname
                        ,u.email
                        ,CASE 
                            WHEN u.activated = 1 THEN 'Active'
                            WHEN u.activated = 0 THEN 'Inactive'
                            ELSE ''
                        END as `status`
                        ,u.created_at ,u.updated_at
                        ,b.name AS branch
                        ,pg.name AS `role`
                    FROM " . $sysInfo->db_name . "." . $sysInfo->tbl_user_name . " u
                    LEFT JOIN ". $sysInfo->db_name . "." . $sysInfo->tbl_branch_name ." b
                        ON b.id = u.location_id
                    LEFT JOIN ". $sysInfo->db_name .".users_groups ug
                        ON ug.user_id = u.id
                    LEFT JOIN ". $sysInfo->db_name . "." . $sysInfo->tbl_role_name ." pg
                        ON pg.id = ug.group_id;
                ");
            }
            else if ($sysInfo->slug == "web_admin")
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    SELECT u.user_name AS username
                        ,u.full_name AS fullname
                        ,u.email
                        , case when u.locked = 0 then 'Active' when u.locked = 1 then 'Inactive' ELSE '' END AS 'status'
                        ,u.create_datetime as created_at
                        ,u.branch
                        ,ar.role_title
                    FROM " . $sysInfo->db_name . "." . $sysInfo->tbl_user_name . " u
                    LEFT JOIN " . $sysInfo->db_name . ".admin_user_role aur 
                        ON aur.adm_id = u.adm_id
                    LEFT JOIN ". $sysInfo->db_name . "." . $sysInfo->tbl_role_name ." ar 
                        ON ar.adr_id = aur.adr_id;
                ");
            }
            else if ($sysInfo->slug == "custodian_system")
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    SELECT usercd AS username
                        ,displayname AS fullname
                        ,mail AS email
                        ,CASE WHEN accountlock = 0 THEN 'Active' WHEN accountlock = 1 THEN 'Inactive' ELSE '' END AS 'status'
                        ,role
                        ,branch
                        ,createddate AS created_at
                        ,createdby AS created_by
                        ,modifieddate AS updated_at
                        ,modifiedby AS updated_by
                    FROM ". $sysInfo->db_name . "." . $sysInfo->tbl_user_name .";
                ");

                $no = 1;
                /// looping data
                foreach ($tempData as $temp)
                {
                    $tempBranchData = "";
                    foreach ($this->arr_custodian_system__branch as $temp2)
                    {
                        if ($temp->branch == $temp2["id"])
                        {
                            $tempBranchData = $temp2["name"];
                            break;
                        }
                    }

                    $data->push([
                        "no" => $no++,
                        "name" => $temp->username ?? null,
                        "fullname" => $temp->fullname ?? null,
                        "email" => $temp->email ?? null,
                        "status" => $temp->status ?? null,
                        "role" => $temp->role ?? null,
                        "branch" => $tempBranchData ?? null,
                        "created_by" => $temp->created_by ?? null,
                        "updated_by" => $temp->updated_by ?? null,
                        "created_at" => $temp->created_at ?? null,
                        "updated_at" => $temp->updated_at ?? null
                    ]);
                }

                return $data;
            }
            else if ($sysInfo->slug == "telesale_support")
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    SELECT IFNULL(ldap_username, usercd) AS username
                        ,name as fullname
                        ,email
                        ,CASE WHEN status = 1 THEN 'Active' WHEN status = 0 THEN 'Inactive' ELSE '' END as `status`
                        ,created_at
                        ,updated_at
                        ,branch
                        ,role
                    FROM ". $sysInfo->db_name . "." . $sysInfo->tbl_user_name .";

                ");
            }
            else if ($sysInfo->slug == "report_exporter")
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    SELECT IFNULL(u.usercd, u.ldap_username) as username
                    ,u.name as fullname
                    ,u.email
                    ,CASE WHEN u.status = 1 THEN 'Active' WHEN u.status = 0 THEN 'Inactive' ELSE '' END as status
                    ,u.created_at
                    ,u.updated_at
                    ,u.role
                    ,b.name as branch
                    FROM ". $sysInfo->db_name . "." . $sysInfo->tbl_user_name ." u
                    LEFT JOIN ". $sysInfo->db_name . ".". $sysInfo->tbl_branch_name ." b
                        ON b.code = u.branch
                ");
            }
            else if ($sysInfo->slug == "collateral_management")
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    
                ");
            }
            else if ($sysInfo->slug == "assessment_tool" || $sysInfo->slug == "incident_management")
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    SELECT u.`name` as username
                        ,u.fullname
                        ,u.email
                        ,CASE 
                            WHEN u.status = 1 THEN 'Active'
                            WHEN u.status = 0 THEN 'Inactive'
                            ELSE '' 
                        END as `status`
                        ,b.name AS branch, r.`name` AS `role`
                        ,u.created_at ,u.updated_at
                    FROM " . $sysInfo->db_name . "." . $sysInfo->tbl_user_name . " u
                    LEFT JOIN " . $sysInfo->db_name . "." . $sysInfo->tbl_branch_name . " b 
                        ON b.id = u.branch_id
                    LEFT JOIN " . $sysInfo->db_name . "." . $sysInfo->tbl_role_name . " r
                        ON r.id = u.role_id ;
                ");
            }
            else 
            {
                $tempData = DB::connection("mysql_ddm")->select("
                    SELECT u.`name` as username
                        ,u.fullname
                        ,u.email
                        ,CASE 
                            WHEN u.status = 1 THEN 'Active'
                            WHEN u.status = 0 THEN 'Inactive'
                            ELSE '' 
                        END as `status`
                        ,b.branch_name AS branch, r.`name` AS `role`
                        ,u.created_at ,u.updated_at, u.created_by, u.updated_by
                    FROM " . $sysInfo->db_name . "." . $sysInfo->tbl_user_name . " u
                    LEFT JOIN " . $sysInfo->db_name . "." . $sysInfo->tbl_branch_name . " b 
                        ON b.id = u.branch_id
                    LEFT JOIN " . $sysInfo->db_name . "." . $sysInfo->tbl_role_name . " r
                        ON r.id = u.role_id ;
                ");
            }

            
            $no = 1;
            /// looping data
            foreach ($tempData as $temp)
            {
                $data->push([
                    "no" => $no++,
                    "name" => $temp->username ?? null,
                    "fullname" => $temp->fullname ?? null,
                    "email" => $temp->email ?? null,
                    "status" => $temp->status ?? null,
                    "role" => $temp->role ?? null,
                    "branch" => $temp->branch ?? null,
                    "created_by" => $temp->created_by ?? null,
                    "updated_by" => $temp->updated_by ?? null,
                    "created_at" => $temp->created_at ?? null,
                    "updated_at" => $temp->updated_at ?? null
                ]);
            }

        }


        return $data;
    }


    # CASE WHEN status = 1 THEN "Active" WHEN status = 0 THEN "Inactive" ELSE "" END as status,
    # CASE WHEN activated = 1 THEN "Active" WHEN activated = 0 THEN "Inactive" ELSE "" END as status,
    private $default__select_user = 'SELECT name, fullname, email, CASE WHEN status = 1 THEN "Active" WHEN status = 0 THEN "Inactive" ELSE "" END as status, role_id, branch_id , created_at, updated_at FROM ';
    private $default__select_user2 = "
        SELECT u.`name`
        ,u.fullname
        ,u.email
        ,CASE 
            WHEN u.status = 1 THEN 'Active'
            WHEN u.status = 0 THEN 'Inactive'
            ELSE '' 
        END as `status`
        ,b.branch_name AS branch, r.name AS `role`
        ,u.created_at ,u.updated_at
        FROM users u
        left JOIN branches b
            ON b.id = u.branch_id
        left JOIN roles r
            ON r.id = u.role_id ;
    ";
    private $it_asset_management__select_user = "SELECT username AS `name` , email , CONCAT(first_name , ' ' , last_name ) AS fullname , CASE WHEN activated = 1 THEN 'Active' WHEN activated = 0 THEN 'Inactive' ELSE '' END as status, location_id as branch_id , created_at FROM ";


    private $arr_custodian_system__branch = array(
        ["id"=>"8888", "name"=>"CREDIT CARD ACQUISITION"],
        ["id"=>"1311", "name"=>"HEAD OFFICE"],
        ["id"=>"0012", "name"=>"CHBAR AMPOV BRANCH"],
        ["id"=>"0011", "name"=>"AEON MALL SEN SOK"],
        ["id"=>"0010", "name"=>"POCHENTONG"],
        ["id"=>"0009", "name"=>"SIHANOUKVILLE"],
        ["id"=>"0008", "name"=>"BANTEAY MEANCHEY"],
        ["id"=>"0007", "name"=>"AEON MALL"],
        ["id"=>"0006", "name"=>"BATTAMBONG"],
        ["id"=>"0005", "name"=>"TAKEO"],
        ["id"=>"0004", "name"=>"KOMPONG CHAM"],
        ["id"=>"0003", "name"=>"STUENG MEAN CHEY"],
        ["id"=>"0002", "name"=>"SIEM REAP"],
        ["id"=>"0001", "name"=>"PHNOM PENH BRANCH"],
        ["id"=>"9999", "name"=>"DELIVER TO CUSTOMER"]
    );
}
