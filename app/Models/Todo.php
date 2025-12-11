<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Todo extends Model {
    use SoftDeletes;

    protected $fillable = [
        'title',
        'description',
        'status',
        'due_date',
        'priority',
        'category',
        'file_path',
    ];

    protected $casts = [
        'due_date' => 'date',
        'priority' => 'integer',
    ];

}
