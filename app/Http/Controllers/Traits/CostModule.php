<?php
/**
 * Created by PhpStorm.
 * User: wangxin
 * Date: 2019-04-18
 * Time: 17:57
 *
 */
namespace App\Http\Controllers\Traits;

use App\GeneralOffer;
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
    public function getPriceData(DatabaseManager $db, ProductsManager $manager, $version = null, array $price_ids = []) :array
    {
        $groupFields = $this->groupField($manager->columns);

        $orderField = $manager->orderby;
        $sort = $manager->sort;

        $field = collect($groupFields)->pluck('value')->toArray();
        $sql = "SELECT * FROM (SELECT * FROM {$manager->table} ";

        if ($version) {
            $sql .= " WHERE version = {$version} ";
        }
        else {
            if (count($price_ids) > 0 && !in_array(0, $price_ids)) {
                $sql .= " WHERE id IN  (".implode(",", $price_ids).") ";
            }
        }

        if ($orderField && $sort)
            $sql .= " ORDER BY {$orderField} {$sort} LIMIT 0,99999999999) AS T0 ";
        else
            $sql .= "  LIMIT 0,99999999999) AS T0 ";

        $data = $db->select($sql);

        return (array) $data;
    }

    public function groupField($json) :array
    {
        $column = [];
        $arr = json_decode($json);

        foreach ($arr as $v)
        {
            if (!isset($v->index))
                $v->index = 0;
            array_push($column,["label" => $v->description,"value" => $v->field,"index" => $v->index]);
        }

        return $column;
    }
}