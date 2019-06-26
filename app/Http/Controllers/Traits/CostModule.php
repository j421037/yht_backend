<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-18
 * Time: 17:57
 *
 */
namespace App\Http\Controllers\Traits;

use App\ProductsManager;
use Illuminate\Database\DatabaseManager;
trait CostModule {
    /**
     * price table data
     *
     * @param  Illuminate\Database\DatabaseManager $db
     *
     * @param $table table name
     *
     * @return $data rows
     */
    public function getPriceData(DatabaseManager $db, ProductsManager $manager, $version = null) :array
    {
        $groupFields = $this->groupField($manager->columns);

        $field = collect($groupFields)->pluck('value')->toArray();
        $sql = "SELECT * FROM (SELECT * FROM {$manager->table} ";

        if ($version) {
            $sql .= " WHERE version = {$version} ";
        }

        $sql .= " ORDER BY `created_at` DESC LIMIT 0,99999999999) AS T0 GROUP BY ".implode(",",$field);

        $data = $db->select($sql);

        foreach ($data as $v)
        {
            if ($manager->method == 0)
                $v->unit = "元/条";
            else if ($manager->method == 1)
                $v->unit = "元/吨";
        }

        return (array) $data;
    }

    public function groupField($json) :array
    {
        $column = [];
        $arr = json_decode($json);

        foreach ($arr as $v)
        {
            array_push($column,["label" => $v->description,"value" => $v->field]);
        }

        return $column;
    }
}