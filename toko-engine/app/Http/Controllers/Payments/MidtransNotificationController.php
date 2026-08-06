<?php

namespace App\Http\Controllers\Payments;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\MidtransService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class MidtransNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransService $midtrans): JsonResponse
    {
        $notification = $request->validate([
            'order_id' => ['required', 'string', 'max:50'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'numeric'],
            'signature_key' => ['required', 'string', 'max:512'],
            'merchant_id' => ['nullable', 'string', 'max:100'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'transaction_status' => ['required', 'string', 'max:50'],
            'fraud_status' => ['nullable', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'max:100'],
        ]);

        if (! $midtrans->hasValidSignature($notification) || ! $midtrans->matchesMerchant($notification)) {
            return response()->json(['message' => 'Invalid Midtrans notification signature.'], 403);
        }

        $order = Order::query()->where('number', $notification['order_id'])->first();

        if (! $order instanceof Order) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        if ($order->payment_gateway_code !== MidtransService::GATEWAY_CODE || ! $midtrans->amountMatches($order, $notification)) {
            return response()->json(['message' => 'Notification does not match the order.'], 422);
        }

        $midtrans->applyNotification($order, $notification);

        return response()->json(['message' => 'OK']);
    }
}
