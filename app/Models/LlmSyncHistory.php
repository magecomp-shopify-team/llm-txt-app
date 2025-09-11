<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmSyncHistory extends Model
{
    protected $table = 'llm_sync_histories';

    protected $fillable = [
        'user_id',
        'synced_from',
        'synced_to',
        'products_old', 'products_new', 'products_added', 'products_removed',
        'collections_old', 'collections_new', 'collections_added', 'collections_removed',
        'pages_old', 'pages_new', 'pages_added', 'pages_removed',
        'blogs_old', 'blogs_new', 'blogs_added', 'blogs_removed',
    ];

    protected $casts = [
        'synced_from' => 'datetime',
        'synced_to' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
