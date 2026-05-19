<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'fname',
        'mname',
        'lname',
        'gender',
        'email',           // primary login credential (was "username")
        'verification_code',
        'password',
        // Roles: Administrator | HR Administrator | Payroll Administrator | HR Staff | Payroll Staff
        'role',
        'access',
        'emp_ID',          // links non-admin users to their employee record
    ];

    protected $hidden = [
        'password',
        'remember_token',
        'verification_code',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
    ];

    // ─── Relationships ────────────────────────────────────────────────────────

    /**
     * The employee record this user account belongs to.
     * Non-admin roles are linked via emp_ID → employees.emp_ID.
     */
    public function employee()
    {
        return $this->belongsTo(Employee::class, 'emp_ID', 'emp_ID');
    }

    public function menuPermission()
    {
        return $this->hasOne(UserMenuPermission::class);
    }

    // ─── Role helpers ─────────────────────────────────────────────────────────

    public function hasRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Administrator is the only role with unrestricted access to everything,
     * including System Settings, User Management, and all modules.
     */
    public function isAdministrator(): bool
    {
        return $this->role === 'Administrator';
    }

    public function isHrAdmin(): bool
    {
        return $this->role === 'HR Administrator';
    }

    public function isPayrollAdmin(): bool
    {
        return $this->role === 'Payroll Administrator';
    }

    public function isStaff(): bool
    {
        return in_array($this->role, ['HR Staff', 'Payroll Staff', 'Staff'], true);
    }

    /**
     * Non-admin roles that must be linked to an employee record.
     */
    public function isEmployeeBased(): bool
    {
        return in_array($this->role, ['HR Administrator', 'Payroll Administrator', 'HR Staff', 'Payroll Staff', 'Staff'], true);
    }

    // ─── Accessors ────────────────────────────────────────────────────────────

    public function getFullNameAttribute(): string
    {
        return trim("{$this->fname} {$this->mname} {$this->lname}");
    }
}
