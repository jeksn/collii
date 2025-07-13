<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Tag extends Model
{
    use HasFactory;
    
    protected $fillable = ['name', 'slug', 'color', 'user_id'];
    
    /**
     * Get the feeds associated with this tag
     */
    public function feeds(): BelongsToMany
    {
        return $this->belongsToMany(Feed::class);
    }
    
    /**
     * Get the user that owns the tag
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
