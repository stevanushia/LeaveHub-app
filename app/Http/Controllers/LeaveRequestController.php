<?php

namespace App\Http\Controllers;

use App\Models\LeaveRequest;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;

class LeaveRequestController extends Controller
{
    // Mengambil data Leave Request (Admin lihat semua, User lihat miliknya sendiri)
    public function index(Request $request)
    {
        $user = $request->user();
        $query = LeaveRequest::with(['user', 'leaveType', 'responder']);

        if ($user->role === 'employee') {
            $query->where('user_id', $user->id);
        }

        $requests = $query->orderBy('created_at', 'desc')->get();

        return response()->json(['data' => $requests]);
    }

    public function store(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'leave_type_id' => 'required|exists:leave_types,id',
            'start_date' => 'required|date|after_or_equal:today',
            'end_date' => 'required|date|after_or_equal:start_date',
            'reason' => 'required|string',
        ]);

        // 1. Validasi Overlap (Tidak boleh bentrok dengan pending/approved)
        $overlap = LeaveRequest::where('user_id', $user->id)
            ->whereIn('status', ['pending', 'approved'])
            ->where(function ($query) use ($request) {
                $query->whereBetween('start_date', [$request->start_date, $request->end_date])
                    ->orWhereBetween('end_date', [$request->start_date, $request->end_date])
                    ->orWhere(function ($q) use ($request) {
                        $q->where('start_date', '<=', $request->start_date)
                            ->where('end_date', '>=', $request->end_date);
                    });
            })->exists();

        if ($overlap) {
            throw ValidationException::withMessages([
                'overlap' => 'Tanggal cuti bentrok dengan pengajuan Anda yang lain (Pending/Approved).'
            ]);
        }

        // Hitung Total Hari (Start - End + 1)
        $startDate = Carbon::parse($request->start_date);
        $endDate = Carbon::parse($request->end_date);
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // 2. Validasi Kuota Cukup
        $balance = LeaveBalance::where('user_id', $user->id)
            ->where('leave_type_id', $request->leave_type_id)
            ->where('year', date('Y'))
            ->first();

        if (!$balance || ($balance->total_quota - $balance->used) < $totalDays) {
            throw ValidationException::withMessages([
                'quota' => 'Sisa kuota cuti Anda tidak mencukupi untuk ' . $totalDays . ' hari.'
            ]);
        }

        // 3. Simpan Request
        $leaveRequest = LeaveRequest::create([
            'user_id' => $user->id,
            'leave_type_id' => $request->leave_type_id,
            'start_date' => $request->start_date,
            'end_date' => $request->end_date,
            'total_days' => $totalDays,
            'reason' => $request->reason,
            'status' => 'pending',
        ]);

        return response()->json(['message' => 'Pengajuan cuti berhasil dikirim!', 'data' => $leaveRequest], 201);
    }

    // Admin: Approve Request
    public function approve(Request $request, $id)
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Hanya request pending yang bisa di-approve.'], 400);
        }

        DB::beginTransaction();
        try {
            // 1. Update Status
            $leaveRequest->update([
                'status' => 'approved',
                'responded_by' => $admin->id,
                'responded_at' => now(),
                'admin_notes' => $request->admin_notes
            ]);

            // 2. Kurangi Balance User
            $balance = LeaveBalance::where('user_id', $leaveRequest->user_id)
                ->where('leave_type_id', $leaveRequest->leave_type_id)
                ->where('year', date('Y', strtotime($leaveRequest->start_date)))
                ->first();

            if ($balance) {
                // Pastikan kuota mencukupi sebelum dikurangi (meski sudah divalidasi saat submit)
                if (($balance->total_quota - $balance->used) < $leaveRequest->total_days) {
                    throw ValidationException::withMessages(['general' => 'Sisa kuota tidak mencukupi.']);
                }

                $balance->used += $leaveRequest->total_days;
                $balance->save();
            }

            DB::commit();
            return response()->json(['message' => 'Leave request berhasil di-approve.']);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => $e->getMessage()], 500);
        }
    }

    // Admin: Reject Request
    public function reject(Request $request, $id)
    {
        $admin = $request->user();
        if ($admin->role !== 'admin') {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Hanya request pending yang bisa di-reject.'], 400);
        }

        $leaveRequest->update([
            'status' => 'rejected',
            'responded_by' => $admin->id,
            'responded_at' => now(),
            'admin_notes' => $request->admin_notes
        ]);

        return response()->json(['message' => 'Leave request berhasil di-reject.']);
    }

    // User: Cancel Request
    public function cancel(Request $request, $id)
    {
        $user = $request->user();
        $leaveRequest = LeaveRequest::findOrFail($id);

        if ($leaveRequest->user_id !== $user->id) {
            return response()->json(['message' => 'Anda tidak berhak membatalkan request ini.'], 403);
        }

        if ($leaveRequest->status !== 'pending') {
            return response()->json(['message' => 'Hanya request pending yang bisa dibatalkan.'], 400);
        }

        $leaveRequest->update(['status' => 'cancelled']);

        return response()->json(['message' => 'Leave request berhasil dibatalkan.']);
    }

    // Admin/User: Soft Delete
    public function destroy(Request $request, $id)
    {
        $user = $request->user();
        $leaveRequest = LeaveRequest::findOrFail($id);

        // Aturan 1: Request pending tidak bisa dihapus
        if ($leaveRequest->status === 'pending') {
            return response()->json(['message' => 'Request berstatus pending tidak bisa dihapus. Cancel terlebih dahulu.'], 400);
        }

        // Aturan 2 & 3: Role check
        if ($user->role === 'admin') {
            // Admin bisa hapus semua yang final (approved, rejected, cancelled) - Sudah lolos cek Aturan 1
        } else {
            // User hanya bisa hapus miliknya sendiri yang cancelled atau rejected
            if ($leaveRequest->user_id !== $user->id) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            if ($leaveRequest->status === 'approved') {
                return response()->json(['message' => 'Request yang sudah di-approve tidak bisa dihapus oleh User.'], 400);
            }
        }

        // Isi deleted_by lalu delete (SoftDelete akan mengisi deleted_at otomatis)
        $leaveRequest->deleted_by = $user->id;
        $leaveRequest->save();
        $leaveRequest->delete();

        return response()->json(['message' => 'Data berhasil dihapus.']);
    }
}
