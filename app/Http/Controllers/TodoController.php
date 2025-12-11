<?php

namespace App\Http\Controllers;

use App\Models\Todo;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

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
        \Log::info('Store todo request', $request->all());

        $data = $request->validate([
            'title' => 'required|string|max:150',
            'description' => 'nullable|string',
            'status' => 'nullable|in:pending,in_progress,done',
            'due_date' => 'nullable|date',
            'priority' => 'nullable|integer|min:1|max:3',
            'category' => 'nullable|in:personal,work,study,others',
            'file' => 'nullable|file|max:10240', // max 10MB
        ]);

        \Log::info('Validation passed', $data);

        // Handle file upload
        if ($request->hasFile('file')) {
            \Log::info('File detected');
            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('todos', $fileName, 'public');
            $data['file_path'] = $filePath;
            \Log::info('File stored at', ['path' => $filePath]);
        } else {
            \Log::info('No file in request');
        }

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
        \Log::info('Update todo request', ['id' => $todo->id, 'data' => $request->all()]);

        $data = $request->validate([
            'title' => 'sometimes|required|string|max:150',
            'description' => 'sometimes|nullable|string',
            'status' => 'sometimes|in:pending,in_progress,done',
            'due_date' => 'sometimes|nullable|date',
            'priority' => 'sometimes|integer|min:1|max:3',
            'category' => 'sometimes|in:personal,work,study,others',
            'file' => 'nullable|file|max:10240', // max 10MB
        ]);

        \Log::info('Validation passed for update', $data);

        // Handle file upload
        if ($request->hasFile('file')) {
            \Log::info('File detected in update');
            if ($todo->file_path) {
                try {
                    Storage::disk('public')->delete($todo->file_path);
                    \Log::info('Old file deleted', ['path' => $todo->file_path]);
                } catch (\Exception $e) {
                    \Log::error('Failed to delete old file', ['path' => $todo->file_path, 'error' => $e->getMessage()]);
                }
            }

            $file = $request->file('file');
            $fileName = time() . '_' . $file->getClientOriginalName();
            $filePath = $file->storeAs('todos', $fileName, 'public');
            $data['file_path'] = $filePath;
            \Log::info('New file stored at', ['path' => $filePath]);
        } else {
            \Log::info('No file in update request');
        }

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
