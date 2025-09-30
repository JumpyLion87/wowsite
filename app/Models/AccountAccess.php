<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AccountAccess extends Model
{
    protected $connection = 'mysql_auth';
    protected $table = 'account_access';
    protected $primaryKey = 'id';
}