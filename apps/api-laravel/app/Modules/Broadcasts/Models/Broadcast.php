<?php

namespace App\Modules\Broadcasts\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Broadcast extends Model
{
    use HasFactory;

    protected $guarded = ['id'];
}
