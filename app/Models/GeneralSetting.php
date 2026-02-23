<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GeneralSetting extends Model
{
    protected $fillable = [
        'site_name',
        'contact_email',
        'address',
        'currency_name',
        'currency_icon',
        'currency_rate',
        'base_currency_name',
        'base_currency_icon',
    ];}
