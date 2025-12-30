<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\CartItem;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CartController extends Controller
{
    // عرض محتويات السلة
    public function index()
    {
        $user = Auth::user();
        $cartItems = CartItem::where('user_id', $user->id)
            ->with('product')
            ->get();
        
        $total = 0;
        foreach ($cartItems as $item) {
            $total += $item->quantity * $item->product->price;
        }

        return response()->json([
            'items' => $cartItems,
            'total' => $total,
            'items_count' => $cartItems->count()
        ]);
    }

    // اضافة منتج للسلة
    public function addToCart(Request $request)
    {
        $request->validate([
            'product_id' => 'required|exists:products,id',
            'quantity' => 'nullable|integer|min:1'
        ]);

        $user = Auth::user();
        $product = Product::find($request->product_id);
        
        // التحقق من وجود كمية كافية
        if ($product->quantity < ($request->quantity ?? 1)) {
            return response()->json([
                'message' => 'الكمية غير متاحة'
            ], 400);
        }

        $cartItem = CartItem::where('user_id', $user->id)
            ->where('product_id', $request->product_id)
            ->first();

        if ($cartItem) {
            
            $cartItem->increment('quantity', $request->quantity ?? 1);
            $message = 'تم زيادة كمية المنتج في السلة';
        } else {
           
            $cartItem = CartItem::create([
                'user_id' => $user->id,
                'product_id' => $request->product_id,
                'quantity' => $request->quantity ?? 1
            ]);
            $message = 'تم اضافة المنتج للسلة';
        }

        return response()->json([
            'message' => $message,
            'cart_item' => $cartItem->load('product')
        ], 201);
    }

}
