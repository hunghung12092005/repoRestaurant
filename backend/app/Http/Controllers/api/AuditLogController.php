<?php
// app/Http/Controllers/api/AuditLogController.php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\AuditLog;
use Illuminate\Http\Request;

class AuditLogController extends Controller
{
    public function index(Request $request)
    {
       $query = AuditLog::with('user.role')->latest(); // Lấy kèm thông tin user và sắp xếp mới nhất

        // Lọc theo hành động (event)
        if ($request->filled('event')) {
            $query->where('event', $request->event);
        }

        // Lọc theo ngày
        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        // Tìm kiếm chung
        if ($request->filled('q')) {
            $search = $request->q;
            $query->where(function ($q) use ($search) {
                $q->where('auditable_type', 'like', "%{$search}%")
                  ->orWhereHas('user', function ($userQuery) use ($search) {
                      $userQuery->where('name', 'like', "%{$search}%");
                  });
            });
        }
        
        $logs = $query->paginate(20);

        return response()->json($logs);
    }
}