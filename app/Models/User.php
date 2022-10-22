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
    protected $appends = ["absent_count", "rival"];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    // getSequenceInvoicmentAttribute
    public function getAbsentCountAttribute()
    {
        return  Absent::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("status", 0)->where("user_id", $this->id)->count();
    }
    public function getRivalAttribute()
    {
        $count =  Absent::whereBetween("date", [Carbon::now()->startOfMonth(), Carbon::now()->endOfMonth()])->where("user_id", $this->id)->where("status", 0)->count();
        $role = Role::where("role", "الغياب")->first();

        return  $role->value * $count;
    }
}