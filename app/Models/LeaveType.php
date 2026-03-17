<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LeaveType extends Model
{
    protected $guarded = ['id'];

    // 1. leave_types -> leave_balances (Mendefinisikan quota per type)
    public function leaveBalances()
    {
        return $this->hasMany(LeaveBalance::class);
    }

    // 2. leave_types -> leave_requests (Kategorisasi request berdasarkan leaves_type)
    public function leaveRequests()
    {
        return $this->hasMany(LeaveRequest::class);
    }
}
