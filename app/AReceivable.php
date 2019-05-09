<?php

namespace App;

use App\ARType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class AReceivable extends Model
{
    use SoftDeletes;
    protected $fillable = [
    	'rid',
    	'amountfor',
    	'date',
    	'is_init',
        'remark'
    ];

    public function type()
    {
    	return $this->hasOne('App\ARType', 'id', 'type_id')->select('id','key');
    }
}
