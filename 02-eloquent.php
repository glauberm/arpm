<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Models\CartItem;

class OrderController extends Controller
{
    public function index()
    {
        $ordersQuery = Order::query()
            ->with('customer:id,name')
            ->with('cartItems')
            ->select(['id', 'status', 'customer_id', 'created_at', 'completed_at']);

        $orders = $ordersQuery->get();

        $ordersIds = $orders->pluck('id');

        $lastAddedToCart = CartItem::whereIn('order_id', $ordersIds)
            ->select(['id', 'order_id', 'created_at'])
            ->orderByDesc('created_at')
            ->get();

        $orderData = collect();

        foreach ($orders as $order) {
            $customer = $order->customer;
            $items = $order->items;
            $totalAmount = 0;
            $itemsCount = 0;

            foreach ($items as $item) {
                $totalAmount += $item->price * $item->quantity;
                $itemsCount++;
            }

            $orderData->push([
                'order_id' => $order->id,
                'customer_name' => $customer->name,
                'total_amount' => $totalAmount,
                'items_count' => $itemsCount,
                'last_added_to_cart' => $lastAddedToCart
                    ->firstWhere('order_id', $order->id)
                    ->created_at ?? null,
                'completed_order_exists' => $order->status === 'completed',
                'created_at' => $order->created_at,
                'completed_at' => $order->completed_at,
            ]);
        }

        return view('orders.index', [
            'orders' => $orderData
                ->filter(function ($value, $key) {
                    return $value['completed_order_exists'];
                })
                ->sortByDesc('completed_at')
                ->values()
        ]);
    }
}
