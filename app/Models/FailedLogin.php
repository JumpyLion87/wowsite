<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class FailedLogin extends Model
{
    use HasFactory;

    protected $fillable = [
        'ip_address',
        'username',
        'attempts',
        'last_attempt',
        'block_until'
    ];

    public $timestamps = false; // legacy table uses integer timestamps

    // Связь с пользователем (если используется)
    public function user()
    {
        return $this->belongsTo(User::class, 'username', 'username');
    }
}