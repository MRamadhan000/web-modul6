<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;

class TodoController extends Controller {
    /**
     * Display a listing of the resource.
     */
    public function index(Request $req)
    {
        $q = Todo::query();

        if ($s = $req->query('search')) {
            $q->where(fn($q) => $q->where('title', 'like', "%$s%")->orWhere('description', 'like', "%$s%"));
        }

        if ($status = $req->query('status'))
            $q->where('status', $status);
        if ($cat = $req->query('category'))
            $q->where('category', $cat);
        if ($prio = $req->query('priority'))
            $q->where('priority', $prio);

        $q->latest('created_at');

        $todos = $q->paginate($req->integer('limit', 10));
        return response()->json($todos);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,done',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|integer|min:1|max:3',
            'category' => 'nullable|in:personal,work,study,others',
        ]);

        $todo = Todo::create($data);
        return response()->json($todo, 201);
    }


    /**
     * Display the specified resource.
     */
    public function show(Todo $todo)
    {
        return response()->json($todo);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Todo $todo)
    {
        $data = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:pending,in_progress,done',
            'due_date' => 'sometimes|nullable|date',
            'priority' => 'sometimes|integer|min:1|max:3',
            'category' => 'sometimes|in:personal,work,study,others',
        ]);

        $todo->update($data);
        return response()->json($todo);
    }


    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Todo $todo)
    {
        $todo->delete();

        return response()->json([
            'message' => 'Todo berhasil dihapus',
            'id' => $todo->id
        ], 200);
    }
}
