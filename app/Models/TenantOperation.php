<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class TenantOperation extends Model
{
    protected $fillable = [
        'tenant_id',
        'operation',
        'status',
        'current_step',
        'log',
        'error',
        'started_at',
        'finished_at',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'finished_at' => 'datetime',
    ];

    public function appendLog(string $text): void
    {
        $this->update([
            'log' => ($this->log ?? '') . PHP_EOL . $text,
        ]);

        // Keep the in-memory model in sync
        $this->log = ($this->log ?? '') . PHP_EOL . $text;
    }
}