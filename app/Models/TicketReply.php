<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TicketReply extends Model
{
    protected $table = 'ticket_replies';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'user_id',
        'ticket_id',
        'message',
    ];

    public function admin()
    {
        return $this->belongsTo(Admin::class, 'admin_id');
    }

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function ticket()
    {
        return $this->belongsTo(Ticket::class, 'ticket_id');
    }

    public function file()
    {
        return $this->hasOne(File::class, 'ticket_reply_id');
    }
}
