<?php
namespace App\Http\Controllers;

use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    public function index()
    {
        $tables = Table::all();
        return response()->json(['data' => $tables]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'table_name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $table = Table::create($validated);
        return response()->json(['data' => $table], 201);
    }

    public function update(Request $request, $id)
    {
        $table = Table::where('table_id', $id)->firstOrFail();
        $validated = $request->validate([
            'table_name' => 'required|string|max:255',
            'capacity' => 'required|integer|min:1',
            'status' => 'required|in:active,inactive',
            'location' => 'nullable|string',
            'description' => 'nullable|string',
        ]);

        $table->update($validated);
        return response()->json(['data' => $table]);
    }

    public function destroy($id)
    {
        $table = Table::where('table_id', $id)->firstOrFail();
        $table->delete();
        return response()->json(null, 204);
    }
}
