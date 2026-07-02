<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DeploymentLog extends Model
{
    protected $fillable = [
        'tenant_id',
        'operation',
        'step',
        'status',
        'output',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];
}