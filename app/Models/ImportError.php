<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportError extends Model
{
    use HasFactory;

    protected $fillable = [
        'import_batch_id', 'severity', 'error_type', 'message', 'row_number',
        'field_name', 'suggested_solution', 'resolved',
    ];

    protected $casts = ['resolved' => 'boolean'];
}
