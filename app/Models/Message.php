<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Message extends Model
{
    use HasFactory;

    protected $fillable = [
        'template_id', 
        'recipient', 
        'channel', 
        'status', 
        'twilio_sid', 
        'error_message', 
        'sent_at'
    ];

    protected $casts = [
        'sent_at' => 'datetime',
    ];

    public function template()
    {
        return $this->belongsTo(Template::class);
    }
}
