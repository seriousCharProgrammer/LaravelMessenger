<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class voiceMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'sender_id',
        'receiver_id',
        'file_path',
        'duration',
    ];
    public function sender()
    {
        return $this->belongsTo(User::class, 'id');
    }
}
