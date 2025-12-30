<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Product;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ProductController extends Controller
{
    
    public function index(Request $request)
    {
        $query = Product::with('category');

        
        if ($request->filled('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $products = $query->get(); // بدون pagination وبدون sort

        return response()->json($products);
    }


    
    public function show($id)
    {
        $product = Product::with('category')->find($id);

        if (!$product) {
            return response()->json([
                'message' => 'المنتج غير موجود'
            ], 404);
        }

        return response()->json($product);
    }

   
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'required|string',
            'price' => 'required|numeric|min:0',
            'quantity' => 'required|integer|min:0',
            'category_id' => 'required|exists:categories,id',
            'image' => 'nullable|string'
        ]);

        $product = Product::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'price' => $request->price,
            'quantity' => $request->quantity,
            'category_id' => $request->category_id,
            'image' => $request->image
        ]);

        return response()->json([
            'message' => 'تم اضافة المنتج بنجاح',
            'product' => $product
        ], 201);
    }

        public function update(Request $request, $id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'المنتج غير موجود'
            ], 404);
        }

        
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'price' => 'sometimes|numeric|min:0',
            'quantity' => 'sometimes|integer|min:0',
            'category_id' => 'sometimes|exists:categories,id',
            'image' => 'nullable|string'
        ]);

        
        $product->update($request->only([
            'name',
            'description',
            'price',
            'quantity',
            'category_id',
            'image'
        ]));

        
        if ($request->filled('name')) {
            $product->slug = Str::slug($request->name);
            $product->save();
        }

        return response()->json([
            'message' => 'تم تحديث المنتج بنجاح',
            'product' => $product
        ]);
    }

    
    public function destroy($id)
    {
        $product = Product::find($id);

        if (!$product) {
            return response()->json([
                'message' => 'المنتج غير موجود'
            ], 404);
        }

        $product->delete();

        return response()->json([
            'message' => 'تم حذف المنتج بنجاح'
        ]);
    }

    
    public function filterByCategory($category_id)
    {
        $category = Category::find($category_id);

        if (!$category) {
            return response()->json([
                'message' => 'القسم غير موجود'
            ], 404);
        }

        $products = Product::where('category_id', $category_id)
            ->with('category')->get();

        return response()->json([
            'category' => $category,
            'products' => $products
        ]);
    }

    // بحث عن المنتجات
    public function search(Request $request)
    {
        $request->validate([
            'q' => 'required|string|min:2'
        ]);

        $products = Product::where('name', 'like', '%' . $request->q . '%')
            ->orWhere('description', 'like', '%' . $request->q . '%')
            ->with('category')
            ->paginate(20);

        return response()->json($products);
    }
}


