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

        // Filtering Logic according to status query parameter
        //overdue: due_date < today and done = false
        //open: done = false
        $status = $request->query('status');
        if ($status === 'overdue') {
            $query->where('due_date', '<', now()->toDateString())->where('done', false);
        } elseif ($status === 'open') {
            $query->where('done', false);
        }
        // Return view with tasks and the active filter (status)
        return view('tasks.index', [
            'tasks' => $query->get(),
            'status' => $status,
        ]);
    }
    /**
     * Store a newly created task in the database.
    */
    public function store(Request $request)
    {
        // VALIDATION: Validate the incoming request data
        $request->validate([
            'description' => 'required|string|max:255',
            'due_date' => 'nullable|date|after:today',
        ]);
        // CREATION: Create a new task record in the database
        $task = Task::create([
            'description' => $request->description,
            'due_date' => $request->due_date,
        ]);
        // 🚀 Redirect back to homepage and highlight the new task (using session flash)
        return redirect('/')->with('new_task_id', $task->id);
    }
    /**
     * Update an existing task (mark as done or edit details).
     */
    public function update(Request $request, Task $task)
    {   
        // VALIDATION: Validate the incoming request data
        // - 'done' field is optional, but if present, must be boolean (true/false).
        // - 'description' is required when updating text.
        // - 'due_date' is optional but must be a valid future date.
        $validated = $request->validate([
            'done' => 'sometimes|boolean',
            'description' => 'sometimes|string|required',
            'due_date' => 'sometimes|nullable|date|after:today',
        ]);
        // UPDATE: Update the task with validated data
        $task->update($validated);
        // Redirect back to the task list while keeping the current filter (status)
        // This ensures the user stays on the same filtered view after an update.
        return redirect()->route('tasks.index', ['status' => $request->input('status')]);
    }
}
