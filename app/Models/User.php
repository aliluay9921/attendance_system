<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Traits\Uuids;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, Uuids, SoftDeletes;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $guarded = [];
    protected $dates = ['deleted_at'];
    protected $with = ["shift"];
    protected $appends = ["absent_count", "rival", "num_clock", "bonus", "coustom_bonus", "shift_count"];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    public function getAbsentCountAttribute()
    {
        return  Absent::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("status", 0)->where("user_id", $this->id)->count();
    }
    public function getNumClockAttribute()
    {
        return $sum_num_count =  Attendance::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("user_id", $this->id)->sum("num_clock");
    }
    public function getRivalAttribute()
    {
        $fee = 0;
        $count =  Absent::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("user_id", $this->id)->where("status", 0)->count();
        $role = Role::where("role", "غائب")->first();
        if (isset($role)) {
            $fee +=  $role->value * $count;
        }

        $sum_num_count =  Attendance::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("user_id", $this->id)->sum("num_clock");
        $role = Role::where("role", "تأخير")->first();
        if (isset($role)) {
            $fee +=  $role->value * $sum_num_count;
        }

        return $fee;
    }

    public function getBonusAttribute()
    {
        return  Bonus::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("type", 1)->where("user_id", $this->id)->sum("bonus");
    }
    public function getCoustomBonusAttribute()
    {
        return  Bonus::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("type", 2)->where("user_id", $this->id)->sum("bonus");
    }
    public function getShiftCountAttribute()
    {
        return  Shift::where("user_id", $this->id)->count();
    }


    public function shift()
    {
        return $this->hasMany(Shift::class, 'user_id');
    }
}