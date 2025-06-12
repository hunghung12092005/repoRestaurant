<?php

namespace App\Http\Controllers;

use App\Models\Season;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Log;

class SeasonController extends Controller
{
    public function index()
    {
        try {
            $seasons = Season::all();
            Log::info('Fetched seasons: ' . $seasons->count());
            return response()->json(['data' => $seasons], 200);
        } catch (\Exception $e) {
            Log::error('Error fetching seasons: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi lấy danh sách mùa', 'error' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'season_name' => 'required|string|max:100|unique:seasons',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'hourly_rate' => 'required|numeric|min:0',
            'nightly_rate' => 'required|numeric|min:0',
            'daily_rate' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $season = Season::create($request->all());
            return response()->json(['data' => $season, 'message' => 'Thêm mùa thành công'], 201);
        } catch (\Exception $e) {
            Log::error('Error storing season: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi thêm mùa', 'error' => $e->getMessage()], 500);
        }
    }

    public function update(Request $request, $id)
    {
        $season = Season::find($id);
        if (!$season) {
            return response()->json(['message' => 'Không tìm thấy mùa'], 404);
        }

        $validator = Validator::make($request->all(), [
            'season_name' => 'required|string|max:100|unique:seasons,season_name,' . $id . ',season_id',
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
            'hourly_rate' => 'required|numeric|min:0',
            'nightly_rate' => 'required|numeric|min:0',
            'daily_rate' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0|max:100',
            'description' => 'nullable|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['message' => 'Dữ liệu không hợp lệ', 'errors' => $validator->errors()], 422);
        }

        try {
            $season->update($request->all());
            return response()->json(['data' => $season, 'message' => 'Cập nhật mùa thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Error updating season: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi cập nhật mùa', 'error' => $e->getMessage()], 500);
        }
    }

    public function destroy($id)
    {
        $season = Season::find($id);
        if (!$season) {
            return response()->json(['message' => 'Không tìm thấy mùa'], 404);
        }

        try {
            $season->delete();
            return response()->json(['message' => 'Xóa mùa thành công'], 200);
        } catch (\Exception $e) {
            Log::error('Error deleting season: ' . $e->getMessage());
            return response()->json(['message' => 'Lỗi khi xóa mùa', 'error' => $e->getMessage()], 500);
        }
    }
}
