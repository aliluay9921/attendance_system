<?php

namespace App\Models;

use App\Traits\Uuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Attendance extends Model
{
    use HasFactory, Uuids;
    protected $guarded = [];
    protected $with = ["user"];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}