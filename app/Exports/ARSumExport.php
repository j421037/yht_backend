<?php

/**
 * @author wx
 * 项目导出欠款信息 excel
 */

namespace App\Exports;
use App\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class ARSumExport implements FromCollection, WithHeadings
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        // TODO: Implement headings() method.
        return [
            "年份",
            "月份",
            "期初",
            "销售额",
            "回款额",
            "欠款额",
            "客户名称",
            "项目名称"
        ];
    }
}
