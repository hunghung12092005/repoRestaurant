<?php

namespace App\Http\Controllers;

use App\Models\Image;
use Illuminate\Http\Request;

class UploadController extends Controller
{
    public function create()
    {
        return view('upload'); // Tạo view để tải lên ảnh
    }

    public function store(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $imageName = time() . '.' . $request->image->extension();
        $request->image->move(public_path('images'), $imageName);

        // Lưu đường dẫn vào CSDL
        Image::create([
            'url' => 'images/' . $imageName,
            'alt_text' => 'Mô tả hình ảnh', // Có thể lấy từ input nếu cần
            'menuitem_id' => $request->item_id, // Nếu có liên kết với sản phẩm
        ]);

        return redirect('/upload')->with('success', 'Ảnh đã được tải lên!');
    }
}
