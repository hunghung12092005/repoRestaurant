<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\MenuItems;
use Illuminate\Http\Request;

class MenuItemController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $perPage = 10;
        // Lấy tất cả món ăn cùng với danh mục và hình ảnh
        $menuItems = MenuItems::with(['category', 'images'])->paginate($perPage);

        // Lấy 5 món ăn có giá cao nhất cùng với danh mục và hình ảnh
        $topPriceItems = MenuItems::with(['category', 'images'])
            ->orderBy('Price', 'desc')
            ->take(5)
            ->get();

        // Trả về dữ liệu dưới dạng JSON
        return response()->json([
            'allMenuItems' => $menuItems,
            'topPriceItems' => $topPriceItems,
        ]);
    }
    public function getItemArray(Request $request) {
        // Lấy mảng ID từ request
        $ids = $request->input('ids'); // Đảm bảo rằng mảng ID được gửi dưới dạng JSON
    
        // Lấy tất cả các bản ghi tương ứng với mảng ID
        $items = MenuItems::whereIn('id', $ids)->get();
        
        return response()->json($items); // Trả về danh sách các món dưới dạng JSON
    }
    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }
    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
