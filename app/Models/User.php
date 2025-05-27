<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    protected $table = 'pay_pers';
    protected $primaryKey = 'emp_code';
    public $incrementing = false;
    protected $keyType = 'number';
    protected $connection = 'oracle';
    public $timestamps = false;
    protected $rememberTokenName = false;

    
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'emp_code',
        'u_passwd',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'u_passwd',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
        ];
    }

    public function getAuthIdentifierName()
    {
        return 'emp_code';
    }
    public function getAuthPassword()
    {
        return $this->u_passwd;
    }
    public function setPasswordAttribute($value)
    {
        $this->attributes['password'] = $value;
    }
    public function leaveAuth()
    {
        return $this->hasOne(LeaveAuth::class, 'emp_code_a', 'emp_code');
    }
    public function isHR()
    {
        return in_array($this->desg_code, ['971', '991', '44', '996']);
    }
    public function isBoss()
    {
        if($this?->leaveAuth?->emp_code_a)
        {
            return true;
        }
        return false;
    }
    public function teamMembers()
    {
        return $this->hasMany(LeaveAuth::class, 'emp_code_a', 'emp_code');
    }
    public function attendance()
    {
        return $this->hasMany(Attendance::class, 'emp_code', 'emp_code');
    }
}
