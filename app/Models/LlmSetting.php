<?php
// app/Models/LlmSetting.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LlmSetting extends Model
{
    protected $fillable = [
        'user_id',
        'include_products',
        'include_collections',
        'include_pages',
        'include_blogs',
        'format',
        'sync_frequency',
        'last_synced_at',
        'next_synced_at',
    ];

    public function user() {
        return $this->belongsTo(User::class);
    }
}

