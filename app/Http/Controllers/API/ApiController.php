<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Resources\ProductCategoryResource;
use App\Models\Product;
use App\Models\ProductCategory;
use App\Models\ShippingMethod;
use App\Models\User;
use App\Traits\ApiResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use App\Traits\FileUploadAndDeleteTrait;

class ApiController extends Controller

{
    use FileUploadAndDeleteTrait, ApiResponse;

    public function test()
    {
        return response()->json([
            'status' => 'success',
            'message' => 'This is the first test api :)'
        ], 200);
    }


    // ثبت‌نام
    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:6|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user
        ], 201);
    }

    // ورود
    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'errors' => $validator->errors()
            ], 422);
        }

        if (!Auth::attempt($request->only('email', 'password'))) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Invalid credentials'
            ], 401);
        }

        $user = Auth::user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'token' => $token,
            'user' => $user
        ]);
    }

    //get All Users
    public function getAllUsers()
    {
        $users = User::all();

        if (!$users) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Data not found!'
            ], 400);
        }

        return response()->json([
            'status' => 'success',
            'count' => count($users),
            'data' => $users
        ], 200);
    }

    // Edit Users
    public function editUser($userId, Request $request)
    {
        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'message' => 'User not found!'
            ], 400);

        }

        $validator = Validator::make($request->all(), rules: [
            'name' => 'required|min:4',
            'email' => 'required|email'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ], 400);
        }

        $user->name = $request->name;
        $user->email = $request->email;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'Successfully Updated',
            'data' => $user
        ]);
    }

    public function deleteUser($userId)
    {

        $user = User::find($userId);

        if (!$user) {
            return response()->json([
                'status' => 'fail',
                'messasge' => 'User not found'
            ], 400);
        }

        $user->delete();

        return response()->json([
            'status' => 'sauccess',
            'message' => 'User Successfully deleted',
        ]);
    }


    // Create product category
    public function createCategory(Request $request)
    {
        $validator = Validator::make(
            data: $request->all(),
            rules: [
                'name' => 'required|unique:product_categories,name'
            ]
        );


        if ($validator->fails()) {
            return response()->json(
                data: [
                    'status' => 'fail',
                    'message' => $validator->errors()
                ], status: 400
            );
        }

        $data['name'] = $request->name;
        $data['slug'] = Str::slug($request->name);


        $imagePath = $this->uploadImage($request, 'image');
        $data['image'] = $imagePath ?? null;


        ProductCategory::create($data);


        return response()->json(
            [
                'status' => 'success',
                'message' => 'product category successfully created',
                'data' => $data
            ],
            200
        );
    }

    public function getCategories()
    {
        $categories = ProductCategory::get();

        if (!$categories) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Data Not Found!'
            ]);
        }

        return response()->json([
            'status' => 'success',
            'count' => count($categories),
            'data' => $categories
        ]);
    }

    public function editCategory($categoryId, Request $request)
    {
        $category = ProductCategory::find($categoryId);

        if (!$category) {
            return response()->json(
                [
                    'status' => 'fail',
                    'message' => 'Data not found!'
                ]
            );
        }

        $validator = Validator::make(
            data: $request->all(),
            rules: ['name' => 'required']
        );

        if ($validator->fails()) {
            return response()->json([
                'status' => 'fail',
                'message' => $validator->errors()
            ]);
        }

        $category->name = $request->name;
        $category->slug = Str::slug($request->name);
        $imagePath = $this->uploadImage($request, 'image');
        $category->image = $imagePath ?? null;
        $category->save();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'Category Successfully Updated'
            ]
        );

    }

    public function deleteCategory(int $categoryId)
    {

        $category = ProductCategory::find($categoryId);
        if (!$category) {
            return response()->json([
                'status' => 'fail',
                'message' => 'Category Not Found!'
            ], 404);
        }

        $category->delete();

        return response()->json(
            [
                'status' => 'success',
                'message' => 'category ' . $category->name . ' deleted'
            ], 200
        );

    }

    public function createProduct(Request $request)
    {

        $validator = Validator::make(
            $request->all(),
            [
                'name' => 'required|string|max:255',
                'category_id' => 'required|integer|exists:product_categories,id',
                'price' => 'required|numeric|min:0',
                'image' => 'nullable|image|max:2048',
                'description' => 'nullable|string',
                'status' => 'nullable|boolean',
            ]
        );

        if ($validator->fails()) {
            return $this->errorResponse(
                message: $validator->errors(),
                statusCode: 422
            );
        }

        $validated = $validator->validated();

        $productData = [
            'name' => $validated['name'],
            'category_id' => $validated['category_id'],
            'price' => $validated['price'],
            'description' => $validated['description'] ?? null,
            'status' => $validated['status'] ?? true,
            'image' => null
        ];

        if ($request->hasFile('image')) {
            $productData['image'] = $this->uploadImage($request, 'image');
        }

        $product = Product::create($productData);

        return $this->successResponse(
            data: $product,
            message: 'success product created'
        );
    }

    public function getProducts()
    {
        $categories = ProductCategory::with('products')->get();

        if ($categories->isEmpty()) {
            return $this->errorResponse();
        }

        return $this->successResponse(data: ProductCategoryResource::collection($categories));

    }


    public function editProduct($productId, Request $request)
    {
        $product = Product::find($productId);

        if (!$product) {
            return $this->errorResponse();
        }

        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'category_id' => 'required|exists:product_categories,id',
            'price' => 'required|numeric',
            'description' => 'nullable|string',
            'status' => 'nullable|boolean',
            'image' => 'nullable|image|max:2048',
        ]);

        if ($validator->fails()) {
            return $this->errorResponse(
                message: $validator->errors(),
                statusCode: 422
            );
        }

        $validated = $validator->validated();


        $product->fill([
            'name' => $validated['name'],
            'price' => $validated['price'],
            'category_id' => $validated['category_id'],
            'description' => $validated['description'] ?? $product->description,
            'status' => $validated['status'] ?? true,
        ]);


        if ($request->hasFile('image')) {
            $product->image = $this->uploadImage($request, 'image');
        }

        $product->save();

        return $this->successResponse(
            data: $product, message: 'successfully product edited'
        );
    }

    public function deleteProduct(int $productId)
    {

        $product = Product::find($productId);

        if (!$product) {
            return $this->errorResponse();
        }


        $this->removeImage($product->image);
        $product->delete();

        return $this->successResponse(message: 'Product Successfully Deleted');
    }

    public function createShippingMethod(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'shipping_price' => 'required|numeric|min:0',
            'method_code' => 'required|string|max:100',
        ]);

        if ($validator->fails()) return $this->errorResponse(message: $validator->errors());

        $validated = $validator->validated();

        $data = [
            'name' => $validated['name'],
            'status' => $validated['status'] ?? true,
            'shipping_price' => $validated['shipping_price'],
            'method_code' => $validated['method_code']
        ];

        ShippingMethod::create($data);

        return $this->successResponse(message: "Shipping method is successfully created :)");


    }

    public function getAllShippingMethods()
    {
        $items = ShippingMethod::all();

        if ($items->isEmpty()) return $this->errorResponse();

        return $this->successResponse(data: $items);

    }

    public function editShippingMethods(int $shippingId, Request $request)
    {
        $shippingMethod = ShippingMethod::find($shippingId);

        if (!$shippingMethod) return $this->errorResponse();

        $validator = Validator::make(
            data: $request->all(), rules: [
            'name' => 'required',
            'shipping_price' => 'required',
            'method_code' => 'required'
        ]);

        if ($validator->fails()) return $this->errorResponse(message: $validator->errors());

        $validated = $validator->validated();

        $shippingMethod->fill([
            'name' => $validated['name'],
            'shipping_price' => $validated['shipping_price'],
            'method_code' => $validated['method_code'],
            'status' => $validated['status'] ?? true
        ]);

        $shippingMethod->save();

        return $this->successResponse(message: 'shipping method successfully updated');
    }

    public function deleteShippingMethod(int $shippingId)
    {
        $result = ShippingMethod::find($shippingId);

        if (!$result) return $this->errorResponse();

        $result->delete();
        return $this->successResponse(message: 'shipping method successfully deleted');
    }


}
