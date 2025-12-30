<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class CategoryController extends Controller
{
   
    public function index()
    {
        $categories = Category::all();
        return response()->json($categories);
    }

    
    public function show($id)
    {
        $category = Category::with('products')->find($id);
        
        if (!$category) {
            return response()->json([
                'message' => 'القسم غير موجود'
            ], 404);
        }

        return response()->json($category);
    }

    
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255|unique:categories',
            'description' => 'nullable|string',
            'image' => 'nullable|string'
        ]);

        $category = Category::create([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $request->image
        ]);

        return response()->json([
            'message' => 'تم اضافة القسم بنجاح',
            'category' => $category
        ], 201);
    }

    public function update(Request $request, $id)
    {
        $category = Category::find($id);
        
        if (!$category) {
            return response()->json([
                'message' => 'القسم غير موجود'
            ], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:categories,name,'.$id,
            'description' => 'nullable|string',
            'image' => 'nullable|string'
        ]);

        $category->update([
            'name' => $request->name,
            'slug' => Str::slug($request->name),
            'description' => $request->description,
            'image' => $request->image
        ]);

        return response()->json([
            'message' => 'تم تحديث القسم بنجاح',
            'category' => $category
        ]);
    }

    public function destroy($id)
    {
        $category = Category::find($id);
        
        if (!$category) {
            return response()->json([
                'message' => 'القسم غير موجود'
            ], 404);
        }

        $category->delete();

        return response()->json([
            'message' => 'تم حذف القسم بنجاح'
        ]);
    }
}
