<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class ArrearsData extends Model
{
    //
    protected $fillable = [
        "customer_name",
        "customer_id",
        "project_name",
        "project_id",
        "tag",
        "status",
        "contract",
        "work_scope",
        "work_scope_name",
        "attached",
        "user_name",
        "user_id",
        "tax"
    ];
}
