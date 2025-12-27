<?php

namespace WebWizr\AdminPanel\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

class BlogPost extends Model
{
    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'category_id',
        'author_id',
        'is_featured',
        'is_published',
        'published_at',
        'seo_title',
        'seo_description',
    ];

    protected $casts = [
        'is_featured' => 'boolean',
        'is_published' => 'boolean',
        'published_at' => 'datetime',
    ];

    /**
     * Boot the model
     */
    protected static function boot(): void
    {
        parent::boot();

        static::creating(function ($post) {
            if (empty($post->slug)) {
                $post->slug = Str::slug($post->title);
            }
        });

        static::saving(function ($post) {
            // Auto-set published_at when publishing
            if ($post->is_published && !$post->published_at) {
                $post->published_at = now();
            }
        });
    }

    /**
     * Get the category
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'category_id');
    }

    /**
     * Get the author
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * Get tags
     */
    public function tags(): BelongsToMany
    {
        return $this->belongsToMany(BlogTag::class, 'blog_post_tag');
    }

    /**
     * Scope: Published posts only
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('is_published', true)
            ->whereNotNull('published_at')
            ->where('published_at', '<=', now());
    }

    /**
     * Scope: Featured posts
     */
    public function scopeFeatured(Builder $query): Builder
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope: Order by published date
     */
    public function scopeLatest(Builder $query): Builder
    {
        return $query->orderByDesc('published_at');
    }

    /**
     * Get SEO title (fallback to title)
     */
    public function getSeoTitleAttribute($value): string
    {
        return $value ?: $this->title;
    }

    /**
     * Get excerpt (auto-generate from content if empty)
     */
    public function getExcerptAttribute($value): string
    {
        if ($value) {
            return $value;
        }

        return Str::limit(strip_tags($this->content), 160);
    }

    /**
     * Get the featured image URL
     */
    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }

        return asset('storage/' . $this->featured_image);
    }

    /**
     * Get reading time in minutes
     */
    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($wordCount / 200));
    }
}
