<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserInterests extends Model
{
    use HasFactory;

    protected $fillable = ['user_id', 'interest_id'];

    public function interests()
    {
        return $this->hasOne(Interests::class, 'id', 'interest_id');
    }

}
