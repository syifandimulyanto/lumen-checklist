<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Checklist extends Model
{
    // TIME
    const MONTH = 'month';
    const WEEK = 'week';
    const DAY = 'day';
    const HOUR = 'hour';
    const MINUTE = 'minute';

    protected $table = 'checklists';
    protected $fillable = [
        'task_id',
        'object_id',
        'object_domain',
        'description',
        'is_completed',
        'due',
        'urgency',
        'completed_at',
        'last_update_by'
    ];
    protected $appends = ['due_interval', 'due_unit'];


    public $timestamps = true;

    public function task()
    {
        return $this->belongsTo('App\Models\Task');
    }

    public function items()
    {
        return $this->hasMany('App\Models\Item', 'checklist_id');
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

    public static function due($unit, $interval)
    {
        $due = null;
        switch ($unit){
            case self::MONTH:
                $due = Carbon::now()->addMonth($interval);
                break;
            case self::WEEK:
                $due = Carbon::now()->addWeek($interval);
                break;
            case self::DAY:
                $due = Carbon::now()->addDay($interval);
                break;
            case self::HOUR:
                $due = Carbon::now()->addHour($interval);
                break;
            case self::MINUTE:
                $due = Carbon::now()->addMinute($interval);
                break;
            default:
                break;
        }

        return $due;
    }
}