<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\SoftDeletes;

class User extends Authenticatable
{
    use Notifiable;
    use SoftDeletes;
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    // protected $dateFormat = 'U'; //把日期更新的格式改为时间戳
    protected $fillable = [
        'name', 
        'password',
        'phone',
        'department_id',
        'workwx',
        'authorize'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function role()
    {
        return $this->belongsToMany('App\Role')->select('id','name');
    }

    public function customerBack()
    {
        return $this->hasMany('App\Customer', 'user_id')
                    ->join('regions as r1', 'customers.province', '=', 'r1.id')
                    ->join('regions as r2', 'customers.city', '=', 'r2.id')
                    ->join('regions as r3', 'customers.area', '=', 'r3.id')
                    // ->leftJoin('customer_notes as c1', function ($join) {
                    //     $join->on( 'customers.id','=','c1.customer_id')
                    //          ->on( 'customers.user_id','=','c1.user_id')
                    //          ->groupBy('user_id,customer_id');  
                    // })

                    ->leftJoin('brands as b1', 'customers.brand_id', '=', 'b1.id')
                    ->select(
                        [
                            'customers.id',
                            'customers.name',
                            'customers.phone',
                            'customers.wechat',
                            'customers.qq',
                            'customers.project_name',
                            'customers.demand',
                            'customers.description',
                            'customers.accept',
                            'customers.created_at',
                            'customers.updated_at',
                            'r1.region_name as province', 
                            'r2.region_name as city', 
                            'r3.region_name as area',
                            'b1.name as brand_name',
                        ]
                        )
                    // ->orderBy('customers.id', 'desc');
                    ->orderBy('customers.updated_at', 'desc');
    }

    public function department()
    {
        return $this->belongsTo('App\Department');
    }

    public function customer()
    {
        return $this->hasMany('App\Customer', 'user_id')
                    ->leftJoin('customer_notes', function ($join) {
                        $join->on('customers.id', '=', 'customer_notes.customer_id')
                             ->on('customers.user_id', '=', 'customer_notes.user_id')
                             ->where(['customer_notes.action' => 0]);
                    })
                    ->select([
                        'customers.*',
                        'customer_notes.created_at as sort'
                    ]);
    }

}
