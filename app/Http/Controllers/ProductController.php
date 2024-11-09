<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Category;
use App\Models\Tag;
use Illuminate\Http\Request;

class ProductController extends Controller
{
    /**
     * @OA\Post(
     *     path="/api/products",
     *     tags={"Products"},
     *     summary="Add a new product",
     *     description="Creates a new product with features, categories, and tags",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(
     *             required={"name", "price", "stock"},
     *             @OA\Property(property="name", type="string", example="Laptop"),
     *             @OA\Property(property="description", type="string", example="A high-end gaming laptop"),
     *             @OA\Property(property="price", type="number", format="float", example=1500.00),
     *             @OA\Property(property="stock", type="integer", example=10),
     *             @OA\Property(property="is_sponsored", type="boolean", example=true),
     *             @OA\Property(property="company_id", type="integer", example=1),
     *             @OA\Property(property="features", type="array", 
     *                 @OA\Items(
     *                     @OA\Property(property="feature_type", type="string", example="color"),
     *                     @OA\Property(property="feature_value", type="string", example="red")
     *                 )
     *             ),
     *             @OA\Property(property="categories", type="array", 
     *                 @OA\Items(type="integer", example=1)
     *             ),
     *             @OA\Property(property="tags", type="array", 
     *                 @OA\Items(type="integer", example=1)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Product created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="product", type="object")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation Error",
     *         @OA\JsonContent(
     *             @OA\Property(property="errors", type="object")
     *         )
     *     )
     * )
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'price' => 'required|numeric',
            'stock' => 'required|integer',
            'is_sponsored' => 'boolean',
            'company_id' => 'required|exists:companies,id',
            'features' => 'array',
            'features.*.feature_type' => 'required|string',
            'features.*.feature_value' => 'required|string',
            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
            'tags' => 'array',
            'tags.*' => 'exists:tags,id',
        ]);

        $product = Product::create($validated);

        if ($request->has('features')) {
            foreach ($request->features as $feature) {
                $product->features()->create($feature);
            }
        }

        if ($request->has('categories')) {
            $product->categories()->sync($request->categories);
        }

        if ($request->has('tags')) {
            $product->tags()->sync($request->tags);
        }

        return response()->json(['product' => $product], 201);
    }

    /**
 * @OA\Patch(
 *     path="/api/products/{id}/stock",
 *     tags={"Products"},
 *     summary="Update product stock",
 *     description="Update the stock level for a specific product",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"stock"},
 *             @OA\Property(property="stock", type="integer", example=20, description="New stock level")
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Stock updated successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Stock updated successfully"),
 *             @OA\Property(property="product", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */
public function updateStock(Request $request, $id)
{
    $validated = $request->validate([
        'stock' => 'required|integer'
    ]);

    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    $product->update(['stock' => $validated['stock']]);

    return response()->json(['message' => 'Stock updated successfully', 'product' => $product], 200);
}


/**
 * @OA\Patch(
 *     path="/api/products/{id}/sponsored",
 *     tags={"Products"},
 *     summary="Toggle sponsored status",
 *     description="Marks a product as sponsored or removes the sponsored status",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Sponsored status updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Sponsored status updated successfully"),
 *             @OA\Property(property="product", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */
public function toggleSponsoredStatus($id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    $product->is_sponsored = !$product->is_sponsored;
    $product->save();

    return response()->json(['message' => 'Sponsored status updated successfully', 'product' => $product], 200);
}

/**
 * @OA\Post(
 *     path="/api/wishlist",
 *     tags={"Wishlist"},
 *     summary="Add a product to wishlist",
 *     description="Adds a product to the authenticated user's wishlist",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             required={"product_id"},
 *             @OA\Property(property="product_id", type="integer", example=1, description="ID of the product to add to wishlist")
 *         )
 *     ),
 *     @OA\Response(
 *         response=201,
 *         description="Product added to wishlist",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product added to wishlist")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */
public function addToWishlist(Request $request)
{
    $validated = $request->validate([
        'product_id' => 'required|exists:products,id'
    ]);

    $user = auth()->user();
    $user->wishlist()->attach($validated['product_id']);

    return response()->json(['message' => 'Product added to wishlist'], 201);
}

/**
 * @OA\Delete(
 *     path="/api/wishlist/{product_id}",
 *     tags={"Wishlist"},
 *     summary="Remove a product from wishlist",
 *     description="Removes a product from the authenticated user's wishlist",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="product_id",
 *         in="path",
 *         required=true,
 *         description="ID of the product to remove from wishlist",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Product removed from wishlist",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Product removed from wishlist")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found in wishlist"
 *     )
 * )
 */
public function removeFromWishlist($product_id)
{
    $user = auth()->user();

    if (!$user->wishlist()->where('product_id', $product_id)->exists()) {
        return response()->json(['message' => 'Product not found in wishlist'], 404);
    }

    $user->wishlist()->detach($product_id);

    return response()->json(['message' => 'Product removed from wishlist'], 200);
}
/**
 * @OA\Get(
 *     path="/api/wishlist",
 *     tags={"Wishlist"},
 *     summary="Get user's wishlist",
 *     description="Retrieves the list of products in the authenticated user's wishlist",
 *     security={{"bearerAuth":{}}},
 *     @OA\Response(
 *         response=200,
 *         description="Wishlist retrieved successfully",
 *         @OA\JsonContent(
 *             @OA\Property(property="wishlist", type="array", @OA\Items(type="object"))
 *         )
 *     )
 * )
 */
public function listWishlist()
{
    $user = auth()->user();
    $wishlist = $user->wishlist()->with('product')->get();

    return response()->json(['wishlist' => $wishlist], 200);
}
/**
 * @OA\Patch(
 *     path="/api/products/{id}/assign-categories-tags",
 *     tags={"Products"},
 *     summary="Assign categories and tags",
 *     description="Assign or update categories and tags for a specific product",
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         description="Product ID",
 *         @OA\Schema(type="integer", example=1)
 *     ),
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="categories", type="array", @OA\Items(type="integer"), example={1, 2}),
 *             @OA\Property(property="tags", type="array", @OA\Items(type="integer"), example={1, 2})
 *         )
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Categories and tags updated",
 *         @OA\JsonContent(
 *             @OA\Property(property="message", type="string", example="Categories and tags updated successfully"),
 *             @OA\Property(property="product", type="object")
 *         )
 *     ),
 *     @OA\Response(
 *         response=404,
 *         description="Product not found"
 *     )
 * )
 */
public function assignCategoriesAndTags(Request $request, $id)
{
    $product = Product::find($id);

    if (!$product) {
        return response()->json(['message' => 'Product not found'], 404);
    }

    $validated = $request->validate([
        'categories' => 'array|exists:categories,id',
        'tags' => 'array|exists:tags,id'
    ]);

    if ($request->has('categories')) {
        $product->categories()->sync($validated['categories']);
    }

    if ($request->has('tags')) {
        $product->tags()->sync($validated['tags']);
    }

    return response()->json(['message' => 'Categories and tags updated successfully', 'product' => $product], 200);
}

}

