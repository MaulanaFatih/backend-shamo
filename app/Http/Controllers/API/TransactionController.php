<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TransactionController extends Controller
{
    public function all(Request $request)
    {
        $id = $request->input('id');
        $limit = $request->input('limit', 6);
        $status = $request->input('status');

        if ($id) {
            $transaction = Transaction::with(['item.products'])->find($id);
            if ($transaction) {
                return ResponseFormatter::success($transaction, "Data successfully fetched successfully");
            } else {
                return ResponseFormatter::error($transaction, 'Error fetching data', 404);
            }
        }
        $transcation = Transaction::with(['items.product'])->where('users_id', Auth::user()->id);
        if ($status) {
            $transcation->where('status', $status);
        }
        return ResponseFormatter::success(
            $transcation->paginate($limit),
            "Transaction List data successfully FETVHED"
        );
    }
}
