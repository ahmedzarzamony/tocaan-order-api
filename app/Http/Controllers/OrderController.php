<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Resources\OrderResource;
use Illuminate\Support\Facades\Response;


class OrderController extends Controller
{

    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $userId = $request->user()->id;
        $orders = Order::with('items')
        ->where('user_id', $userId)
        ->when($request->filled('status') && in_array($request->status, ['pending', 'confirmed', 'cancelled']), function ($query) use ($request) {
            $query->where('status', $request->status);
        })
        ->paginate(10);
        return OrderResource::collection($orders);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.product_name' => 'required|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
            'items.*.price' => 'required|numeric|min:0',
        ]);

        
        $order = DB::transaction(function () use ($request) {
            $userId = $request->user()->id;
            $total = collect($request->items)->sum(fn($item) => $item['quantity'] * $item['price']);
            $order = Order::create([
                'user_id' => $userId,
                'status' => 'pending',
                'total' => $total,
            ]);
            foreach ($request->items as $item) {
                $order->items()->create([
                    'product_name' => $item['product_name'],
                    'quantity' => $item['quantity'],
                    'price' => $item['price'],
                ]);
            }
            return $order->load('items');
        });

        return Response::json([
            'message' => 'Order created successfully',  
            'order' => new OrderResource($order)
        ], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $order = Order::with('items')->findOrFail($id);
        return new OrderResource($order);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        $order->delete();
        return response()->json(['message' => 'Order deleted successfully'], 200);
    }
}
