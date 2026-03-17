<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\LeaveType;
use App\Models\LeaveBalance;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class UserController extends Controller
{
    // Mengambil data semua user (employee) beserta sisa cutinya
    public function index()
    {
        $users = User::where('role', 'employee')
            ->with(['leaveBalances.leaveType'])
            ->get()
            ->map(function ($user) {
                // Memformat data cuti agar mudah dibaca frontend
                $balances = [];
                foreach ($user->leaveBalances as $balance) {
                    $balances[$balance->leaveType->name] = [
                        'total' => $balance->total_quota,
                        'used' => $balance->used,
                        'remaining' => $balance->total_quota - $balance->used,
                    ];
                }
                $user->formatted_balances = $balances;
                return $user;
            });

        $totalUsers = User::where('role', 'employee')->count();

        return response()->json([
            'users' => $users,
            'total_users' => $totalUsers,
            'max_users' => 2
        ]);
    }

    // Membuat user baru + Auto-assign balance
    public function store(Request $request)
    {
        // 1. Validasi Maksimal 2 User Employee
        $currentCount = User::where('role', 'employee')->count();
        if ($currentCount >= 2) {
            throw ValidationException::withMessages([
                'general' => ['Slot user penuh. Maksimal hanya 2 user yang diperbolehkan.']
            ]);
        }

        // 2. Validasi Input
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8',
        ]);

        DB::beginTransaction();
        try {
            // 3. Create User
            $user = User::create([
                'name' => $request->name,
                'email' => $request->email,
                'password' => Hash::make($request->password),
                'role' => 'employee',
            ]);

            // 4. Auto-Assign Leave Balances
            $leaveTypes = LeaveType::all();
            foreach ($leaveTypes as $type) {
                LeaveBalance::create([
                    'user_id' => $user->id,
                    'leave_type_id' => $type->id,
                    'year' => date('Y'),
                    'total_quota' => $type->default_quota,
                    'used' => 0,
                ]);
            }

            DB::commit();
            return response()->json(['message' => 'User berhasil ditambahkan'], 201);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['message' => 'Terjadi kesalahan pada server'], 500);
        }
    }

    // Edit User (Update Password / Info)
    public function update(Request $request, $id)
    {
        $user = User::findOrFail($id);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email,' . $user->id,
            'password' => 'nullable|string|min:8', // Password opsional saat edit
        ]);

        $user->name = $request->name;
        $user->email = $request->email;
        if ($request->filled('password')) {
            $user->password = Hash::make($request->password);
        }
        $user->save();

        return response()->json(['message' => 'User berhasil diperbarui']);
    }

    public function myBalances(Request $request)
    {
        $currentYear = date('Y');

        $balances = LeaveBalance::where('user_id', $request->user()->id)
            ->where('year', $currentYear)
            ->with('leaveType')
            ->get();

        return response()->json($balances);
    }
}
