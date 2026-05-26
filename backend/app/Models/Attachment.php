<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;
use Illuminate\Support\Facades\Storage;

class Attachment extends Model
{
    protected $fillable = ['user_id', 'name', 'disk_path', 'size_bytes', 'mime_type'];

    public function attachable(): MorphTo { return $this->morphTo(); }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }

    public function getUrlAttribute(): string
    {
        return Storage::disk('s3')->temporaryUrl($this->disk_path, now()->addHours(1));
    }
}
