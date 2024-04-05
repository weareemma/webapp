<?php

namespace App\Services;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;

class OrderService {

    public static function createOrUpdateOrder(User $user, Order $order = null, $total = 0, Request $request = null)
    {
        if (is_null($order))
        {
            $order = new Order();
        }

        // Update user and total
        $order->fill([
            'user_id' => $user->id,
            'total' => $total
        ]);

        $order->data = ($request) ? Arr::except($request->all(), 'available_days') : null;

        if ($total == 0)
        {
            $order->status = Order::STATUS_COMPLETED;
        }

        $order->save();

        return $order->refresh();
    }

    /**
     * Delete order
     */
    public static function deleteOrder(Order $order)
    {
        $order->delete();
    }
}