<?php

namespace App\Http\Controllers;

use App\Http\Resources\ProductResource;
use App\Models\Product;
use Knuckles\Scribe\Attributes\Group;
use Knuckles\Scribe\Attributes\ResponseFromApiResource;

#[Group("Products", 'Product management')]
class ProductController extends Controller
{
    #[ResponseFromApiResource(
        name: ProductResource::class,
        model: Product::class,
        status: 200,
        description: 'Successfully retrieved products.',
        collection: true,
        with: ['ingredients'],
        paginate: true
    )]
    public function __invoke()
    {
        return ProductResource::collection(Product::with('ingredients')->paginate());
    }
}
