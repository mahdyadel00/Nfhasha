<?php

namespace App\Http\Controllers\Api\User;

use App\Http\Controllers\Controller;
use App\Models\Withdrawal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class WithdrawalController extends Controller
{
    // API لإدخال بيانات السحب
    public function store(Request $request)
    {
        $request->validate([
            'bank_name' => 'required|string|max:255',
            'account_number' => 'required|string|max:50',
            'iban' => 'required|string|max:50',
            'amount' => 'required|numeric|min:1',
        ]);

        $user = Auth::user(); // جلب المستخدم المصادق عليه
        if (!$user) {
            return response()->json(['error' => 'يرجى تسجيل الدخول'], 401);
        }

        if ($user->balance < $request->amount) {
            return response()->json(['error' => 'الرصيد غير كافٍ'], 400);
        }

        $withdrawal = Withdrawal::create([
            'user_id' => $user->id,
            'bank_name' => $request->bank_name,
            'account_number' => $request->account_number,
            'iban' => $request->iban,
            'amount' => $request->amount,
            'status' => 'pending',
        ]);

        return response()->json([
            'message' => 'تم تسجيل طلب السحب بنجاح',
            'withdrawal' => $withdrawal,
        ]);
    }

    // API لتأكيد وتنفيذ السحب
    public function confirm($id)
    {
        $user = Auth::user(); // جلب المستخدم المصادق عليه
        if (!$user) {
            return response()->json(['error' => 'يرجى تسجيل الدخول'], 401);
        }

        $withdrawal = Withdrawal::where('user_id', $user->id)->find($id);
        if (!$withdrawal) {
            return response()->json(['error' => 'طلب السحب غير موجود'], 404);
        }

        if ($withdrawal->status !== 'pending') {
            return response()->json(['error' => 'تمت معالجة الطلب مسبقًا'], 400);
        }

        if ($user->balance < $withdrawal->amount) {
            $withdrawal->update(['status' => 'failed']);
            return response()->json(['error' => 'الرصيد غير كافٍ'], 400);
        }

        // خصم المبلغ من الرصيد
        $user->balance -= $withdrawal->amount;
        $user->save();

        // تحديث حالة السحب
        $withdrawal->update(['status' => 'completed']);

        return response()->json([
            'message' => 'تم تنفيذ السحب بنجاح',
            'new_balance' => $user->balance,
            'withdrawal' => $withdrawal,
        ]);
    }
}
