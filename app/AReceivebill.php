<?php

namespace App;

use App\ARType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AReceivebill extends Model
{
    use SoftDeletes;
    protected $fillable = [
    	'cust_id',
    	'pid',
    	'amountfor',
        'discount',
    	'date',
        'remark',
    ];

    public function type()
    {
    	return $this->hasOne('App\ARType', 'id', 'type_id')->select('id','key');
    }
}
