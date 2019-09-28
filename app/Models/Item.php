<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Item extends Model
{
    // TIME
    const MONTH = 'month';
    const WEEK = 'week';
    const DAY = 'day';
    const HOUR = 'hour';
    const MINUTE = 'minute';

    protected $table = 'items';
    protected $fillable = [
        'user_id',
        'checklist_id',
        'assignee_id',
        'task_id',
        'is_completed',
        'due',
        'urgency',
        'completed_at',
        'last_update_by'
    ];
    protected $appends = ['due_interval', 'due_unit'];

    public $timestamps = true;

    public function checklist()
    {
        return $this->belongsTo('App\Models\Checklist');
    }

    public function task()
    {
        return $this->belongsTo('App\Models\Task');
    }

    public function getDueIntervalAttribute()
    {
        if ($this->due !== null) {
            $created = Carbon::parse($this->created_at);
            $due = Carbon::parse($this->due);

            $month = $due->diffInMonths($created);
            if ($month > 1 ) return $month;

            $week = $due->diffInWeeks($created);
            if ($week > 1) return $week;

            $day = $due->diffInDays($created);
            if ($day > 1) return $day;

            $hour = $due->diffInHours($created);
            if ($hour > 1) return $hour;

            $minute = $due->diffInMinutes($created);
            if ($minute > 1) return $minute;
        }
    }

    public function getDueUnitAttribute()
    {
        if ($this->due !== null) {
            $created = Carbon::parse($this->created_at);
            $due = Carbon::parse($this->due);

            $month = $due->diffInMonths($created);
            if ($month > 1 ) return self::MONTH;

            $week = $due->diffInWeeks($created);
            if ($week > 1) return self::WEEK;

            $day = $due->diffInDays($created);
            if ($day > 1) return self::DAY;

            $hour = $due->diffInHours($created);
            if ($hour > 1) return self::HOUR;

            $minute = $due->diffInMinutes($created);
            if ($minute > 1) return self::MINUTE;
        }
    }
}