<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Mail extends Model
{
    protected $connection = 'mysql_char';
    protected $table = 'mail';
    protected $primaryKey = 'id';
    public $incrementing = true;
    public $timestamps = false;

    protected $fillable = [
        'messageType', 'stationery', 'mailTemplateId', 'sender', 'receiver',
        'subject', 'body', 'has_items', 'expire_time', 'deliver_time',
        'money', 'cod', 'checked'
    ];

    protected $casts = [
        'messageType' => 'integer',
        'stationery' => 'integer',
        'mailTemplateId' => 'integer',
        'sender' => 'integer',
        'receiver' => 'integer',
        'has_items' => 'integer',
        'expire_time' => 'integer',
        'deliver_time' => 'integer',
        'money' => 'integer',
        'cod' => 'integer',
        'checked' => 'integer',
    ];

    public function items()
    {
        return $this->hasMany(MailItem::class, 'mail_id', 'id');
    }
}
