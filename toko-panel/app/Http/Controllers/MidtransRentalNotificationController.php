<?php

namespace App\Http\Controllers;

use App\Models\RentalOrder;
use App\Services\MidtransRentalService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use RuntimeException;

class MidtransRentalNotificationController extends Controller
{
    public function __invoke(Request $request, MidtransRentalService $midtrans): JsonResponse
    {
        $notification = $request->validate([
            'order_id' => ['required', 'string', 'max:50'],
            'status_code' => ['required', 'string', 'max:10'],
            'gross_amount' => ['required', 'numeric'],
            'signature_key' => ['required', 'string', 'max:512'],
            'transaction_id' => ['nullable', 'string', 'max:255'],
            'transaction_status' => ['required', 'string', 'max:50'],
            'fraud_status' => ['nullable', 'string', 'max:50'],
            'payment_type' => ['nullable', 'string', 'max:100'],
            'merchant_id' => ['nullable', 'string', 'max:100'],
        ]);

        if (! $midtrans->hasValidSignature($notification) || ! $midtrans->hasExpectedMerchant($notification)) {
            return response()->json(['message' => 'Invalid Midtrans notification signature.'], 403);
        }

        $order = RentalOrder::query()->where('number', $notification['order_id'])->first();
        if (! $order instanceof RentalOrder) {
            return response()->json(['message' => 'Order not found.'], 404);
        }

        try {
            $midtrans->applyNotification($order, $notification);
        } catch (RuntimeException $exception) {
            return response()->json(['message' => $exception->getMessage()], 422);
        }

        return response()->json(['message' => 'OK']);
    }
}
