<?php
namespace App\Http\Controllers;

use App\Models\Menu;
use Illuminate\Http\Request;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::all();
        return response()->json(['data' => $menus]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'menu_name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $menu = Menu::create($validated);
        return response()->json(['data' => $menu], 201);
    }

    public function update(Request $request, $id)
    {
        $menu = Menu::where('menu_id', $id)->firstOrFail();
        $validated = $request->validate([
            'menu_name' => 'required|string|max:255',
            'category' => 'required|string',
            'price' => 'required|numeric|min:0',
            'description' => 'nullable|string',
            'status' => 'required|in:active,inactive',
        ]);

        $menu->update($validated);
        return response()->json(['data' => $menu]);
    }

    public function destroy($id)
    {
        $menu = Menu::where('menu_id', $id)->firstOrFail();
        $menu->delete();
        return response()->json(null, 204);
    }
}
