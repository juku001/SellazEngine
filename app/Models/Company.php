<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'name',
        'abbr',
        'logo',
        'description',
        'primary_color',
        'secondary_color',
        'background_color',
        'text_color'
    ];


    public function user()
    {
        return $this->hasMany(User::class, 'company_id');
    }
}
