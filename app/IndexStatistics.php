<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class IndexStatistics extends Model
{
    protected $fillable = [
    	'target',
    	'completed',
    	'debt',
		'debt_percent',
    	'user_id',
    	'target_client',
        'report_client',
		'coop_client',
    	'lose_client',
    	'brand_price',
    	'rt_price',
    	'other_price',
        'machine',
		'brand_price',
    	'censor',
    	'mynote',
        'likes'
    ];

}
