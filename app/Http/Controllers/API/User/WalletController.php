<?php

namespace App\Http\Controllers\API\User;

use App\Http\Controllers\Controller;
use App\Http\Resources\API\TransactionsRequest;
use Illuminate\Http\Request;
use App\Models\User;

class WalletController extends Controller
{
    public function index()
    {
        /** @var User $user */
        $user = auth()->user();
        $balance = $user->balance;

        $transactions = $user->walletDeposits()
            ->latest()
            ->get();

        return apiResponse(200, __('messages.data_returned_successfully', ['attr' => __('messages.wallet')]), [
            'balance'       => $balance,
            'transactions'  => TransactionsRequest::collection($transactions)
        ]);
    }


    public function deposit(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes'  => 'nullable|string|max:255'
        ]);

        $user = auth()->user();

        $user->increment('balance', $request->amount);
        $user->walletTransactions()->create([

            'amount'    => $request->amount,
            'type'      => 'deposit',
            'notes'     => $request->notes
        ]);

        return apiResponse(200, __('manage.created_successfully', ['attr' => __('messages.deposit')]));
    }

    public function withdraw(Request $request)
    {
        $request->validate([
            'amount' => 'required|numeric|min:1',
            'notes'  => 'nullable|string|max:255'
        ]);

        $user = auth()->user();

        if ($user->balance < $request->amount) {
            return apiResponse(400, __('messages.insufficient_balance'));
        }

        $user->decrement('balance', $request->amount);
        $user->walletTransactions()->create([
            'amount' => $request->amount,
            'type' => 'withdraw',
            'notes' => $request->notes
        ]);
        $user->withdrawals()->create([
            'amount' => $request->amount,
            'notes' => $request->notes
        ]);

        return apiResponse(200, __('manage.created_successfully', ['attr' => __('messages.withdraw')]));
    }
}
