<?php

namespace App\Http\Controllers\API;

use App\Helpers\ResponseFormatter;
use App\Http\Controllers\Controller;
use App\Models\Transaction;
use App\Models\TransactionItem;
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

    public function checkout(Request $request)
    {
        $request->validate([
            'items' => 'required|array',
            'items.*.id' => 'exists:products,id',
            'total_price' => 'required',
            'shipping_price' => 'required',
            'status' => 'required|in:PENDING,SUCCESS,CANCELED,FAILED,SHIPPING,SHIPPED',
        ]);

        $transaction = Transaction::create([
            'users_id' => Auth::user()->id,
            'address' => $request->address,
            'total_price' => $request->total_price,
            'shipping_price' => $request->shipping_price,
            'status' => $request->status,
        ]);

        // Check if $request->items is not null before iterating
        if (!empty($request->items)) {
            foreach ($request->items as $product) {
                TransactionItem::create([
                    'users_id' => Auth::user()->id,
                    'products_id' => $product['id'],
                    'transactions_id' => $transaction->id,
                    'quantity' => $product['quantity']
                ]);
            }
        }

        return ResponseFormatter::success($transaction->load('items.product'), 'Transaction success');
    }

}
