<?php

namespace App\Http\Controllers;

use App\Services\CommentService;
use App\Http\Resources\CommentResource;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function __construct(protected CommentService $commentService) {}

    public function store(Request $request)
    {
        $validated = $request->validate([
            'task_id' => 'required|exists:tasks,id',
            'content' => 'required|string|min:3',
        ]);

        $comment = $this->commentService->addComment($validated);

        return new CommentResource($comment);
    }
}