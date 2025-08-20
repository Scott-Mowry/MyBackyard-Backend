<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Subscription extends Model
{
    use HasFactory;

    protected $table = 'subscriptions';

    protected $fillable = [
        'name',
        'role',
        'type',
        'price',
        'is_depreciated',
        'description',
        'billing_cycle',
        'is_popular',
        'status',
        'on_show',
        'sort_order'
    ];

    protected $casts = [
        'price' => 'decimal:2',
        'is_depreciated' => 'boolean',
        'is_popular' => 'boolean',
        'on_show' => 'boolean',
        'sort_order' => 'integer',
    ];

    // Scopes for common queries
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeVisible($query)
    {
        return $query->where('on_show', true);
    }

    public function scopePopular($query)
    {
        return $query->where('is_popular', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc');
    }

    public function scopeByRole($query, $role)
    {
        return $query->where('role', $role);
    }

    public function scopeByBillingCycle($query, $cycle)
    {
        return $query->where('billing_cycle', $cycle);
    }

    // Relationships
    public function sub_points()
    {
        return $this->hasMany(sub_points::class, 'sub_id', 'id');
    }

    // Accessors
    public function getFormattedPriceAttribute()
    {
        return '$' . number_format($this->price, 2);
    }

    public function getIsActiveAttribute()
    {
        return $this->status === 'active';
    }
}
