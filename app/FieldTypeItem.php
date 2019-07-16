<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class FieldTypeItem extends Model
{
    protected $fillable = ["field","table_id","key","value","user_id"];
}
