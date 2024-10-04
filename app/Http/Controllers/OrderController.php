<?php

namespace App\Http\Controllers;

use App\Actions\Order\CreateOrderAction;
use App\Http\Requests\StoreOrderRequest;
use App\Http\Resources\OrderResource;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;
use Symfony\Component\HttpFoundation\Response;

#[Group(name: 'Orders', description: 'Order management')]
class OrderController extends Controller
{
    /**
     * @param StoreOrderRequest $request
     * @param CreateOrderAction $action
     * @return JsonResponse
     */
    #[ResponseFromApiResource(OrderResource::class, Order::class, 201, 'Create a new Order', with: ['items', 'items.product'])]
    #[\Knuckles\Scribe\Attributes\Response(['message' => 'There are invalid products in the order',], 400, 'Invalid products in the order')]
    #[\Knuckles\Scribe\Attributes\Response(['message' => 'Ingredient Onion is out of stock',], 400, 'Ingredient out of stock')]
    public function __invoke(StoreOrderRequest $request, CreateOrderAction $action): JsonResponse
    {
        try {
            /**
             * @var array<array{product_id: int, quantity: int}> $products
             */
            $products = $request->get('products');
            $order = $action->handle($products);
            return response()->json(new OrderResource($order->load('items', 'items.product')), Response::HTTP_CREATED);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], Response::HTTP_BAD_REQUEST);
        }
    }
}
