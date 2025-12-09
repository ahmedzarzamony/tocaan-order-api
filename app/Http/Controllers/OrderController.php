<?php

namespace App\Http\Controllers;

use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Http\Requests\OrderRequest;
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
        $orders = Order::with('items', 'payments')
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
    public function store(OrderRequest $request)
    {

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
    public function update(OrderRequest $request, string $id)
    {
        $order = Order::with('items')->findOrFail($id);
        if ($order->payments()->exists()) {
            return Response::json([
                'message' => 'Cannot update order with existing payments.'
            ], 403);
        }

        DB::transaction(function () use ($request, $order) {

            $existingItemIds = $order->items()->pluck('id')->toArray(); 
            $requestItemIds = collect($request->items)->pluck('id')->filter()->toArray();
            $itemsToDelete = array_diff($existingItemIds, $requestItemIds); 
            if (!empty($itemsToDelete)) { 
                $order->items()->whereIn('id', $itemsToDelete)->delete(); 
            }

            $newItems = [];

            foreach ($request->items as $itemData) {
                if (isset($itemData['id'])) {
                    $orderItem = $order->items->firstWhere('id', $itemData['id']);

                    if ($orderItem) {
                        $newValues = [
                            'product_name' => $itemData['product_name'],
                            'quantity' => $itemData['quantity'],
                            'price' => $itemData['price'],
                        ];

                        $orderItem->fill($newValues);

                        if ($orderItem->isDirty()) {
                            $orderItem->save();
                        }
                    }

                } else {
                    $newItems[] = [
                        'product_name' => $itemData['product_name'],
                        'quantity' => $itemData['quantity'],
                        'price' => $itemData['price'],
                    ];
                }
            }

            if (!empty($newItems)) {
                $order->items()->createMany($newItems);
            }

            
            
            
        });
        $order->load('items');
        $total = $order->items->sum(fn($i) => $i->quantity * $i->price);
        $order->total = $total;
        $order->save();
         // refresh the items relationship

        return Response::json([
            'message' => 'Order updated successfully',
            'order' => new OrderResource($order)
        ], 200);


    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $order = Order::findOrFail($id);
        if ($order->payments()->exists()) {
            return Response::json([
                'message' => 'Cannot delete order with existing payments.'
            ], 403);
        }
        $order->delete();
        return Response::json(['message' => 'Order deleted successfully'], 200);
    }
}
