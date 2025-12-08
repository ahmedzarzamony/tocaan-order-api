<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\PaymentResource;
use Illuminate\Support\Facades\Response;
use App\Services\Payment\PaymentGatewayFactory;
use App\Services\Payment\PaymentGatewayInterface;


class PaymentController extends Controller
{
    public function listPayments(Request $request)
    {
        $payments = Payment::with('order')
        ->when($request->filled('order_id'), function ($query) use ($request) {
            $query->where('order_id', $request->order_id);
        })->paginate(10);

        return PaymentResource::collection($payments);
    }

    public function payOrder(Request $request)
    {
        $request->validate([
            'order_id' => 'required|exists:orders,id',
            'payment_method' => 'required|in:credit_card,paypal',
        ]);

        $order = Order::findOrFail($request->order_id);

        $lastPayment = $order->latestPayment;

        if ($lastPayment && $lastPayment->status === 'successful') {
            return response()->json([
                'message' => 'Order has already been paid.'
            ], 403);
        }

        if ($order->status !== 'pending') {
            return response()->json([
                'message' => 'Payments can only be processed for confirmed orders.'
            ], 403);
        }

        $gateway = PaymentGatewayFactory::make($request->payment_method);
        if($gateway instanceof PaymentGatewayInterface === false) {
            return response()->json([
                'message' => 'Unsupported payment method.'
            ], 403);
        }

        DB::transaction(function () use ($request, $order, $gateway) {

            $payment = Payment::create([
                'order_id' => $order->id,
                'payment_method' => $request->payment_method,
                'amount' => $order->total,
                'status' => 'pending',
            ]);

            $success = $gateway->process($payment);

            $payment->status = $success ? 'successful' : 'failed';
            $payment->save();

            if ($success) {
                $order->status = 'confirmed'; 
                $order->save();
            }
        });

        return Response::json([
            'message' => 'Payment processed successfully.',
        ], 200);
    }
}
