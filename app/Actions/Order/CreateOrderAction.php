<?php

namespace App\Actions\Order;

use App\Actions\Ingredients\DecreaseQuantityAction;
use App\Enums\OrderStatus;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use Exception;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

final readonly class CreateOrderAction
{

    /**
     * Create a new order
     *
     * @param array<array{product_id: int, quantity: int}> $orderItems
     * @return Order
     * @throws Exception
     */
    public function handle(array $orderItems): Order
    {
        $product_ids = array_column($orderItems, 'product_id');
        /** @var Collection<int, Product> $products */
        $products = Product::whereIn('id', $product_ids)->with('ingredients')->get();

        if ($products->count() !== count($product_ids)) {
            throw new Exception("There are invalid products in the order");
        }

        $this->checkStockAvailability($products, $orderItems);

        DB::beginTransaction();
        try {
            $order = Order::create([
                'status' => OrderStatus::Placed,
            ]);

            foreach ($orderItems as $orderItem) {
                $product = $products->firstWhere('id', $orderItem['product_id']);
                $order->items()->create([
                    'product_id' => $product?->id,
                    'quantity' => $orderItem['quantity'],
                ]);

                /**
                 * @var Collection<int, Ingredient> $ingredients
                 */
                $ingredients = $product?->ingredients;
                foreach ($ingredients as $ingredient) {
                    (new DecreaseQuantityAction())->handle($ingredient, $orderItem['quantity']);
                }
            }
            DB::commit();
            return $order;
        } catch (Exception $e) {
            DB::rollBack();
            throw $e;
        }
    }

    /**
     * @param Collection<int, Product> $products
     * @param array<array{product_id: int, quantity: int}> $orderItems
     * @return void
     * @throws Exception
     */
    private function checkStockAvailability(Collection $products, array $orderItems): void
    {
        $requestedQuantities = [];
        foreach ($orderItems as $orderItem) {
            $requestedQuantities[$orderItem['product_id']] = $orderItem['quantity'];
        }
        foreach ($products as $product) {
            foreach ($product->ingredients as $ingredient) {
                $currentStockToBase = $ingredient->stock * $ingredient->unit?->conversion_factor_to_base;
                $requestedQuantity = $requestedQuantities[$product->id] ?? 0;

                $requiredStockToBase = $requestedQuantity * $ingredient->pivot?->quantity;

                if ($currentStockToBase < $requiredStockToBase) {
                    throw new Exception("Ingredient {$ingredient->title} is out of stock");
                }
            }
        }
    }
}
