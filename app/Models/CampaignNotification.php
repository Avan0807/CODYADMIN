<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CampaignNotification extends Model
{
    use HasFactory;

    protected $table = 'campaign_notifications';

    protected $fillable = [
        'title',
        'message',
        'target_audience'
    ];
}
