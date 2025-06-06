<?php

namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\ShopOnline;
use Illuminate\Http\Request;

class ShopOnlineController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $items = ShopOnline::all();
        return response()->json($items);
    }
    public function getIteam50k()
    {
        $items = ShopOnline::where('price', '>', 50000)->get();
        return response()->json($items);
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
    public function show($id)
    {
        $item = ShopOnline::find($id);
        if (!$item) {
            return response()->json(['message' => 'Sản phẩm không tồn tại'], 404);
        }
        return response()->json($item);
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
