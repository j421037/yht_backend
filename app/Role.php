<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Role extends Model
{
   /**关联表名**/
    protected $table = 'roles';
    /**白名单字段**/
    protected $fillable = array(
    	'name','display_name', 'description'
    );

    public function permission()
    {
    	return $this->belongsToMany('App\Permission')->select('id','name', 'front_path','backend_path', 'node_type', 'pid');
    }
}
