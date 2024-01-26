<?php

namespace App\Http\Controllers;

use App\Http\Resources\CategoryResource;
use App\Models\Category;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\Rule;

class CategoryController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth:api', ['except' => ['create', 'read', 'update', 'delete']]);
    }

    public function create(Request $request): JsonResponse
    {
        $validate = Validator::make($request->all(), [
            'name' => 'required|string|min:2|unique:categories,name',
            'category_id' => 'nullable|exists:categories,id',
        ]);

        if (!$validate->fails()) {
            Category::create($validate->validated());
            return response()->json(['success' => true, 'message' => 'New category has been added successfully.']);
        }

        return response()->json(['success' => false, 'message' => $validate->errors()->first()]);
    }

    public function read(int $categoryId): JsonResponse
    {
        if ($category = Category::find($categoryId)) {
            return response()->json(['category' => new CategoryResource($category)]);
        }
        return response()->json(['success' => false, 'message' => 'Category not found.']);
    }

    public function update(Request $request, int $categoryId): JsonResponse
    {
        if ($category = Category::find($categoryId)) {
            $validate = Validator::make($request->all(), [
                'name' => [
                    'required',
                    'string',
                    'min:2',
                    Rule::unique('categories', 'name')->ignore($category->id)
                ],
                'category_id' => 'nullable|exists:categories,id',
            ]);

            if (!$validate->fails()) {
                $requestCategory = $request->get('category_id');

                if (!is_null($requestCategory)) {
                    if ($requestCategory == $category->id or in_array($requestCategory, $category->children()->pluck('id')->toArray())) {
                        return response()->json(['success' => false, 'message' => 'The selected category id is invalid.']);
                    }
                }

                $category->update($validate->validated());

                return response()->json(['success' => true, 'message' => 'Category has been updated successfully.']);
            }
            return response()->json(['success' => false, 'message' => $validate->errors()->first()]);
        }

        return response()->json(['success' => false, 'message' => 'Category not found.']);
    }

    public function delete(int $categoryId): JsonResponse
    {
        if ($category = Category::find($categoryId)) {
            $category->delete();
            return response()->json(['success' => true, 'message' => 'Category has been deleted.']);
        }
        return response()->json(['success' => false, 'message' => 'Category not found.']);
    }
}
