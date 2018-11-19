<?php

namespace App;

use Auth;
use App\User;
use App\Department;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Project extends Model
{
    use SoftDeletes;

    protected $fillable = [
    	'name',
    	'cust_id',
    	'user_id',
        'payment_days',
        'tid',
        'tag',
        'attachment_id',
        'tax',
        'affiliate',
        'estimate',
        'phone_num',
        "agreement"
    ];


    /**
    *创建汇总数据
    *@param $filter 过滤条件
    */
    public function buildARSum($filter)
    {
    	
    	//创建临时表
    	$receivable = 'temp'.uniqid();
    	$receivebill = 'temp'.uniqid();
        $refund = 'temp'.uniqid();
    	$sum = 'temp'.uniqid();

    	$WHERE = "WHERE 1 = 1 ";

    	//人员过滤 鉴权 = 0 查看当前部门所有成员的客户和项目
    	if ($filter->user_id == 0) {
    		/**如果是超级管理员**/
    		$uid = Auth::user()->id;
    		$user = User::find($uid);
    		$userList = [];
    		$department = Department::find($user->department_id);
    		$role = $user->role()->get()->pluck('name')->toArray();

    		if ($user->group == 'admin') {

    			$userList = User::all()->pluck('id')->toArray();

    		} else if (in_array('报价员', $role) || $department->user_id == $uid) {
    			/**如果是报价员或者部门经理**/
    			$userList = User::where(['department_id' => $department->id])->get()->pluck('id')->toArray();
    		} 

	    	$userList = implode(',', $userList);

    		$WHERE .= " AND pro.user_id in (".$userList.") ";

    	} else {

    		$WHERE .= " AND pro.user_id = ".$filter->user_id." ";
    	}

    	/**客户名称过滤**/
    	if ($filter->cust_id > 0) {

    		$WHERE .= " AND pro.cust_id = ".$filter->cust_id." ";
    	}

    	/**项目名称过滤**/
    	if ($filter->pid > 0 ) {

    		$WHERE .= " AND pro.id = ".$filter->pid." ";
    	}
    	/**合同状态过滤**/
    	if ($filter->agreement < 2 ) {

    		$WHERE .= " AND pro.agreement = ".$filter->agreement." ";
    	}

    	$receivableTempSql = "CREATE TEMPORARY TABLE ".$receivable." (
			`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`name` varchar(256) NOT NULL ,
			`pid` INT NOT NULL ,
			`amountfor` DECIMAL(23,3) 
    	) ENGINE=InnoDB COLLATE 'utf8mb4_unicode_ci'";
    	
    	$receivebillTempSql = "CREATE TEMPORARY TABLE ".$receivebill." (
			`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`name` varchar(256) NOT NULL ,
			`pid` INT NOT NULL ,
			`real_amountfor` DECIMAL(23,3) ,
            `discount` DECIMAL(23,3)  
    	) ENGINE=InnoDB COLLATE 'utf8mb4_unicode_ci'";

        $refundTempSql = "CREATE TEMPORARY TABLE ".$refund." (
            `id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
            `name` varchar(256) NOT NULL ,
            `pid` INT NOT NULL ,
            `refund` DECIMAL(23,3)  
        ) ENGINE=InnoDB COLLATE 'utf8mb4_unicode_ci'";

    	$sumTempSql = "CREATE TEMPORARY TABLE ".$sum." (
			`id` INT NOT NULL PRIMARY KEY AUTO_INCREMENT,
			`cust_id` INT NOT NULL ,
			`name` varchar(256) NOT NULL ,
			`user_name` varchar(256) NOT NULL ,
			`pid` INT NOT NULL ,
			`user_id` INT NOT NULL ,
			`project` varchar(256) NOT NULL ,
			`amountfor` DECIMAL(23,3) ,
			`real_amountfor` DECIMAL(23,3) ,
            `discount` DECIMAL(23,3) ,
            `refund` DECIMAL(23,3) ,
			`balance` DECIMAL(23,3) ,
            `agreement` varchar(256) DEFAULT NULL ,
            `payment_days` varchar(256) DEFAULT NULL,
            `type` varchar(256) DEFAULT NULL ,
            `tax`  varchar(256) DEFAULT NULL

    	) ENGINE=InnoDB COLLATE 'utf8mb4_unicode_ci'";

    	DB::select($receivableTempSql);
    	DB::select($receivebillTempSql);
        DB::select($refundTempSql);
    	DB::select($sumTempSql);

    	$receivableInsertSql = "INSERT INTO {$receivable} (`pid`,  `name`, `amountfor` ) 
								SELECT
									P1.id AS pid,
									MAX( P1.name ) AS name,
									SUM( R1.amountfor ) AS amountfor FROM projects AS P1
								LEFT JOIN a_receivables AS R1 ON R1.pid = P1.id
								Group BY pid
								";

		$receivebillInsertSql = "INSERT INTO {$receivebill} (`pid`,  `name`, `real_amountfor`, `discount` ) 
								SELECT
									P1.id AS pid,
									MAX( P1.name ) AS name,
									SUM( R1.amountfor ) AS real_amountfor ,
                                    SUM( R1.discount ) AS discount 
                                FROM projects AS P1      
								LEFT JOIN a_receivebills AS R1 ON R1.pid = P1.id
								Group BY pid
								";
        $refundInsertSql = "INSERT INTO {$refund} (`pid`,  `name`, `refund` ) 
                                SELECT
                                    P1.id AS pid,
                                    MAX( P1.name ) AS name,
                                    SUM( R1.refund ) AS refund 
                                FROM projects AS P1      
                                LEFT JOIN refunds AS R1 ON R1.pid = P1.id
                                Group BY pid
                                ";

		$sumInsertSql = "INSERT INTO {$sum} ( `cust_id`, `user_name`, `name`,`pid`,`user_id`, `agreement`,`type`,`tax`,`payment_days` ,`project`,`amountfor`, `real_amountfor`, `discount`, `refund`, `balance`) ".
						"SELECT ".
						"cus.id AS cust_id,".
						"MAX(cus.user_name) AS user_name, ".
						"MAX( cus.name ) AS name,".
						"pro.id AS pid ," .
						"pro.user_id AS user_pid ," .
						"pro.agreement AS agreement, " .
                        "pro.type AS type, ".
                        "pro.tax AS tax, ".
                        "pro.payment_days AS payment_days, " .
						"MAX( pro.name ) AS project," .
						"SUM( R1.amountfor ) AS amountfor,".
						"SUM( R2.real_amountfor ) AS real_amountfor,".
                        "SUM( R2.discount ) AS discount,".
                        "SUM( IFNULL(R3.refund, 0) ) AS refund,".
						"( SUM( IFNULL( R1.amountfor, 0 ) ) - SUM( IFNULL( R2.real_amountfor, 0 ) ) - SUM( IFNULL( R2.discount, 0 ) ) - SUM( IFNULL( R3.refund, 0 ) ) ) AS balance ".
						"FROM ".
						"projects AS pro ".
						"INNER JOIN ( SELECT U1.*,U2.name AS user_name FROM real_customers AS U1 INNER JOIN users AS U2 ON U1.user_id = U2.id ) AS cus ON pro.cust_id = cus.id ".
						"LEFT JOIN {$receivable} AS R1 ON R1.pid = pro.id ".
						"LEFT JOIN {$receivebill} AS R2 ON R2.pid = pro.id ".
                        "LEFT JOIN {$refund} AS R3 ON R3.pid = pro.id ".
						$WHERE.
						" GROUP BY pid";

    	DB::select($receivableInsertSql);
    	DB::select($receivebillInsertSql);
        DB::select($refundInsertSql);
    	DB::select($sumInsertSql);

    	//排序
    	$filterOrder = explode('_', $filter->order);
    	$order = " ";

    	if (strtolower($filterOrder[0]) == 'amountfor') {

    		$order = " ORDER BY amountfor ASC";

    		if (strtolower($filterOrder[1]) == 'desc') {
    			$order = " ORDER BY amountfor DESC";
    		}
    	}

    	if (strtolower($filterOrder[0]) == 'realamountfor') {
    		$order = " ORDER BY real_amountfor ASC";

    		if (strtolower($filterOrder[1]) == 'desc') {
    			$order = " ORDER BY real_amountfor DESC";
    		}
    	}

    	$list = [];
    	$list['data'] = DB::select("SELECT * FROM {$sum} LIMIT {$filter->offset}, {$filter->limit} ".$order);
    	$total = DB::select("SELECT COUNT(*) AS total FROM {$sum}");
    	$list['summaries'] = DB::select("SELECT SUM(amountfor)  AS amountfor, SUM(real_amountfor) AS real_amountfor, SUM(discount) AS discount, SUM(refund) AS refund FROM {$sum}");
    	$list['total'] = $total[0]->total;

    	return $list;
    }
}
