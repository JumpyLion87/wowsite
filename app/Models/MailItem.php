<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailItem extends Model
{
    protected $connection = 'mysql_char';
    protected $table = 'mail_items';
    protected $primaryKey = 'mail_id';
    public $incrementing = false;
    public $timestamps = false;

    protected $fillable = [
        'mail_id', 'item_guid', 'receiver'
    ];

    protected $casts = [
        'mail_id' => 'integer',
        'item_guid' => 'integer',
        'receiver' => 'integer',
    ];

    public function mail()
    {
        return $this->belongsTo(Mail::class, 'mail_id', 'id');
    }
}
