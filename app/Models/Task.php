<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    protected $table    = 'tasks';
    protected $fillable = ['name'];

    public $timestamps  = true;

    public function checklist()
    {
        return $this->hasOne('App\Models\Checklist', 'task_id');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Item', 'task_id');
    }
}