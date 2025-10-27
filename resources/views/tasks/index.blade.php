@extends('layout')

@section('content')
<div class="card w-full">
    <section>
        <form class="form grid gap-6" method="POST" action="{{ route('tasks.store') }}">
            @csrf
            <div class="grid gap-2">
                <label for="task_description">Description</label>
                <div class="grid grid-cols-[1fr_180px] gap-2">
                    <input type="text" id="task_description" name="description" placeholder="describe the task..." tabindex="1" autofocus value="{{ old('description') }}">
                    <button type="submit" class="btn" tabindex="3">Add</button>
                </div>
                @error('description')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>

            <div class="grid gap-2">
                <label for="task_due_date">Due date</label>
                <input type="date" id="task_due_date" name="due_date" tabindex="2" value="{{ old('due_date') }}">
                @error('due_date')
                    <p class="text-red-500 text-sm">{{ $message }}</p>
                @enderror
            </div>
        </form>
    </section>

    <hr>

    <section>
        <form class="form flex gap-2 mb-6" method="GET" action="{{ route('tasks.index') }}">
            <label for="filter_status">Filter tasks</label>
            <select id="filter_status" name="status">
                <option value="" {{ !$status ? 'selected' : '' }}>All</option>
                <option value="open" {{ $status === 'open' ? 'selected' : '' }}>Open</option>
                <option value="overdue" {{ $status === 'overdue' ? 'selected' : '' }}>Overdue</option>
            </select>
            <button type="submit" class="btn">Filter</button>
        </form>

        <ul class="grid gap-4">
            @foreach($tasks as $task)
            <li class="flex items-center gap-4">
                <div class="flex flex-col gap-1 mr-auto">
                    <p class="text-sm font-medium leading-none {{ $task->done ? 'line-through' : '' }}">{{ $task->description }}</p>
                    @if($task->due_date)
                        <p class="text-sm font-muted leading-none {{ $task->done ? 'line-through' : '' }}">{{ $task->due_date->format('d-m-Y') }}</p>
                    @endif
                </div>

                @if(!$task->done)
                <form class="form" method="POST" action="{{ route('tasks.update', $task) }}">
                    @csrf
                    @method('PATCH')
                    <button type="submit" class="btn-sm-outline">Done</button>
                </form>
                @endif
            </li>
            @endforeach
        </ul>
    </section>
</div>
@endsection
