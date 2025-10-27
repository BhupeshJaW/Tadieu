<?php

namespace App\Http\Controllers;

use App\Models\Task;
use Illuminate\Http\Request;

class TaskController extends Controller
{
    public function index(Request $request)
    {
        $query = Task::query();

        // Ordering: tasks with due dates first (oldest first), then without due dates (latest created_at first), done last
        $query->orderByRaw('CASE WHEN due_date IS NOT NULL THEN 0 ELSE 1 END')
              ->orderBy('due_date')
              ->orderBy('created_at', 'desc')
              ->orderBy('done');

        // Filtering
        $status = $request->query('status');
        if ($status === 'overdue') {
            $query->where('due_date', '<', now()->toDateString())->where('done', false);
        } elseif ($status === 'open') {
            $query->where('done', false);
        }
        return view('tasks.index', [
            'tasks' => $query->get(),
            'status' => $status,
        ]);
    }
    public function store(Request $request)
    {
        $request->validate([
            'description' => 'required|string|max:255',
            'due_date' => 'nullable|date|after:today',
        ]);

        $task = Task::create([
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);

        return redirect('/')->with('new_task_id', $task->id);
    }

    public function update(Request $request, Task $task)
    {
        $validated = $request->validate([
            'done' => 'sometimes|boolean',
            'description' => 'sometimes|string|required',
            'due_date' => 'sometimes|nullable|date|after:today',
        ]);

        $task->update($validated);

        return redirect()->route('tasks.index', ['status' => $request->query('status')]);
    }
}
