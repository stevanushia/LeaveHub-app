<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use HasFactory, Notifiable;

    protected $guarded = ['id'];

    // 1. users -> leave_balances
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // 2. users -> leave_requests (user_id) - User submit leave request
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'user_id');
    }

    // 3. users -> leave_requests (responded_by) - Admin approve/reject
    public function respondedRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'responded_by');
    }

    // 4. users -> leave_requests (deleted_by) - Siapa yang hapus (soft delete)
    public function deletedRequests()
    {
        return $this->hasMany(LeaveRequest::class, 'deleted_by')->withTrashed();
    }
}
