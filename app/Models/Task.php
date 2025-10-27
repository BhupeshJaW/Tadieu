<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Task extends Model
{
    /** @use HasFactory<\Database\Factories\TaskFactory> */
    use HasFactory;
    //added fillable due_date and done
    protected $fillable = [
        'description',
        'due_date',
        'done',
    ];

    protected $casts = [
        'due_date' => 'date',
    ];
}
