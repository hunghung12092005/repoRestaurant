<?php

    namespace App\Http\Controllers;

    use App\Models\RoomType;
    use Illuminate\Http\Request;
    use Illuminate\Support\Facades\Validator;

    class RoomTypeController extends Controller
    {
        public function index()
        {
            try {
                $roomTypes = RoomType::all();
                return response()->json(['success' => true, 'data' => $roomTypes], 200);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
        }

        public function store(Request $request)
        {
            $validator = Validator::make($request->all(), [
                'type_name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'bed_count' => 'required|integer|min:1',
                'max_occupancy' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            try {
                $roomType = RoomType::create($request->all());
                return response()->json(['success' => true, 'data' => $roomType], 201);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
        }

        public function update(Request $request, $id)
        {
            $validator = Validator::make($request->all(), [
                'type_name' => 'required|string|max:100',
                'description' => 'nullable|string',
                'bed_count' => 'required|integer|min:1',
                'max_occupancy' => 'required|integer|min:1',
            ]);

            if ($validator->fails()) {
                return response()->json(['success' => false, 'message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
            }

            try {
                $roomType = RoomType::findOrFail($id);
                $roomType->update($request->all());
                return response()->json(['success' => true, 'data' => $roomType], 200);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
        }

        public function destroy($id)
        {
            try {
                $roomType = RoomType::findOrFail($id);
                $roomType->delete();
                return response()->json(['success' => true, 'message' => 'Xóa thành công'], 200);
            } catch (\Exception $e) {
                return response()->json(['success' => false, 'message' => 'Lỗi: ' . $e->getMessage()], 500);
            }
        }
    }
?>