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
            <li class="flex items-center gap-4" id="task-{{ $task->id }}"  {{ session('new_task_id') == $task->id ? 'animate-pulse bg-green-50' : '' }}">
                <div class="flex flex-col gap-1 mr-auto">
                    <p class="text-sm font-medium leading-none {{ $task->done ? 'line-through' : '' }}" onclick="editTask({{ $task->id }}, 'description')" id="desc-{{ $task->id }}" style="cursor: pointer;">{{ $task->description }}</p>
                    @if($task->due_date)
                        <p class="text-sm font-muted leading-none {{ $task->done ? 'line-through' : '' }}" onclick="editTask({{ $task->id }}, 'due_date')" id="date-{{ $task->id }}" style="cursor: pointer;">{{ $task->due_date->format('d-m-Y') }}</p>
                    @else
                        <p class="text-sm font-muted leading-none {{ $task->done ? 'line-through' : '' }}" onclick="editTask({{ $task->id }}, 'due_date')" id="date-{{ $task->id }}" style="cursor: pointer;">No due date</p>
                    @endif
                </div>

                @if(!$task->done)
                <form class="form" method="POST" action="{{ route('tasks.update', $task) }}">
                    @csrf
                    @method('PATCH')
                    <input type="hidden" name="status" value="{{ $status }}">
                    <input type="hidden" name="done" value="1">
                    <button type="submit" class="btn-sm-outline">Done</button>
                </form>
                @endif
            </li>
            @endforeach
        </ul>
    </section>
</div>

<script>
function editTask(taskId, field) {
    const element = document.getElementById(field === 'description' ? `desc-${taskId}` : `date-${taskId}`);
    const originalText = element.textContent;
    const isDate = field === 'due_date';

    let input;
    if (isDate) {
        input = document.createElement('input');
        input.type = 'date';
        input.value = originalText !== 'No due date' ? new Date(originalText.split('-').reverse().join('-')).toISOString().split('T')[0] : '';
    } else {
        input = document.createElement('input');
        input.type = 'text';
        input.value = originalText;
    }

    input.className = 'text-sm';
    input.style.width = '100%';

    element.replaceWith(input);
    input.focus();

    function saveEdit() {
        const newValue = input.value;
        const form = document.createElement('form');
        form.method = 'POST';
        form.action = `/tasks/${taskId}`;
        form.style.display = 'none';

        const csrf = document.createElement('input');
        csrf.type = 'hidden';
        csrf.name = '_token';
        csrf.value = document.querySelector('meta[name="csrf-token"]').getAttribute('content') || '{{ csrf_token() }}';
        form.appendChild(csrf);

        const method = document.createElement('input');
        method.type = 'hidden';
        method.name = '_method';
        method.value = 'PATCH';
        form.appendChild(method);

        const statusInput = document.createElement('input');
        statusInput.type = 'hidden';
        statusInput.name = 'status';
        statusInput.value = '{{ $status }}';
        form.appendChild(statusInput);

        const fieldInput = document.createElement('input');
        fieldInput.type = 'hidden';
        fieldInput.name = field;
        fieldInput.value = newValue;
        form.appendChild(fieldInput);

        document.body.appendChild(form);
        form.submit();
    }

    input.addEventListener('blur', saveEdit);
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') {
            saveEdit();
        } else if (e.key === 'Escape') {
            input.replaceWith(element);
        }
    });
}
</script>
@endsection
