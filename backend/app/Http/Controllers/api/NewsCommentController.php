<?php
namespace App\Http\Controllers\api;

use App\Http\Controllers\Controller;
use App\Models\NewsComment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class NewsCommentController extends Controller
{
    public function index(Request $request)
    {
        $query = $request->query('q');
        $comments = NewsComment::with(['user', 'news'])
            ->when($query, function ($q) use ($query) {
                $q->where('comment_text', 'like', $query)
                  ->orWhereHas('user', function ($q) use ($query) {
                      $q->where('name', 'like', $query);
                  })
                  ->orWhereHas('news', function ($q) use ($query) {
                      $q->where('title', 'like', $query);
                  });
            })
            ->get();

        return response()->json($comments);
    }

    public function commentsByNewsId($newsId)
    {
        $comments = NewsComment::where('news_id', $newsId)->with(['user', 'news'])->get();
        return response()->json($comments);
    }

    public function store(Request $request, $newsId)
    {
        $request->validate([
            'comment_text' => 'required|string',
            'status' => 'in', ['0', '1'],
        ]);

        $comment = NewsComment::create([
            'news_id' => $newsId,
            'user_id' => Auth::id(),
            'comment_text' => $request->comment_text,
            'comment_date' => now(),
            'status' => $request->status ?? 1,
        ]);

        return response()->json($comment->load(['user', 'news']), 201);
    }

    public function update(Request $request, $id)
    {
        $comment = NewsComment::findOrFail($id);

        $request->validate([
            'comment_text' => 'required|string',
            'status' => 'in', ['0', '1'],
        ]);

        $comment->update($request->only(['comment_text', 'status']));
        return response()->json($comment->load(['user', 'news']));
    }

    public function destroy($id)
    {
        $comment = NewsComment::findOrFail($id);
        $comment->delete();
        return response()->json(null, 204);
    }
}