<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class File extends Model
{
    protected $table = 'files';

    public $timestamps = false;

    protected $fillable = [
        'admin_id',
        'user_id',
        'ticket_id',
        'ticket_reply_id',
        'filename',
        'originalname',
        'type',
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
        return $this->hasOne(Ticket::class, 'file_id');
    }

    public function ticket_reply()
    {
        return $this->belongsTo(TicketReply::class, 'ticket_reply_id');
    }


    //new
    public function deposit()
    {
        return $this->hasOne(Deposit::class, 'file_id');
    }

    //new
    public function transfer()
    {
        return $this->hasOne(Transfer::class, 'file_id');
    }

    //new
    public function transaction()
    {
        return $this->hasOne(Transaction::class, 'file_id');
    }

    //pm - 1.7
    public function document_verification()
    {
        return $this->hasOne(DocumentVerification::class, 'file_id');
    }

    //pm - 1.9
    public function bank()
    {
        return $this->hasOne(Bank::class, 'file_id');
    }


}
