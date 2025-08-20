<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class BusinessPromoCode extends Model
{
    use HasFactory;

    protected $table = 'promo_codes';

    protected $fillable = [
        'code',
        'name',
        'description',
        'discount_type',
        'discount_value',
        'free_days',
        'usage_limit',
        'usage_count',
        'per_user_limit',
        'starts_at',
        'expires_at',
        'target_role',
        'applicable_subscriptions',
        'status',
        'is_featured',
        'sort_order',
        'created_by',
        'admin_notes'
    ];

    protected $casts = [
        'discount_value' => 'decimal:2',
        'free_days' => 'integer',
        'usage_limit' => 'integer',
        'usage_count' => 'integer',
        'per_user_limit' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'applicable_subscriptions' => 'array',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
        'created_by' => 'integer'
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function subscriptions()
    {
        return $this->belongsToMany(Subscription::class, 'promo_code_subscriptions', 'promo_code_id', 'subscription_id');
    }

    // Usage tracking relationship (you may want to create this table later)
    // public function usages()
    // {
    //     return $this->hasMany(PromoCodeUsage::class, 'promo_code_id');
    // }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeValid($query)
    {
        $now = Carbon::now();
        return $query->where('status', 'active')
            ->where(function ($q) use ($now) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('expires_at')->orWhere('expires_at', '>=', $now);
            });
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeForRole($query, $role)
    {
        return $query->where(function ($q) use ($role) {
            $q->where('target_role', $role)->orWhere('target_role', 'Both');
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order', 'asc')->orderBy('created_at', 'desc');
    }

    public function scopeNotExpired($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')->orWhere('expires_at', '>=', Carbon::now());
        });
    }

    public function scopeNotExhausted($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('usage_limit')->orWhereRaw('usage_count < usage_limit');
        });
    }

    // Accessors
    public function getIsValidAttribute()
    {
        $now = Carbon::now();

        // Check status
        if ($this->status !== 'active') {
            return false;
        }

        // Check start date
        if ($this->starts_at && $this->starts_at > $now) {
            return false;
        }

        // Check expiry date
        if ($this->expires_at && $this->expires_at < $now) {
            return false;
        }

        // Check usage limit
        if ($this->usage_limit && $this->usage_count >= $this->usage_limit) {
            return false;
        }

        return true;
    }

    public function getIsExpiredAttribute()
    {
        return $this->expires_at && $this->expires_at < Carbon::now();
    }

    public function getIsExhaustedAttribute()
    {
        return $this->usage_limit && $this->usage_count >= $this->usage_limit;
    }

    public function getFormattedDiscountAttribute()
    {
        switch ($this->discount_type) {
            case 'percentage':
                return $this->discount_value . '%';
            case 'fixed_amount':
                return '$' . number_format($this->discount_value, 2);
            case 'free_trial':
                return $this->free_days . ' days free';
            default:
                return 'N/A';
        }
    }

    public function getRemainingUsesAttribute()
    {
        if (!$this->usage_limit) {
            return 'Unlimited';
        }

        return max(0, $this->usage_limit - $this->usage_count);
    }

    // Methods
    public function canBeUsedBy($user)
    {
        // Check if promo is valid
        if (!$this->is_valid) {
            return false;
        }

        // Check role targeting
        if ($this->target_role !== 'Both' && $user->role !== $this->target_role) {
            return false;
        }

        // Check per-user limit (you'd need to implement usage tracking)
        // if ($this->per_user_limit) {
        //     $userUsageCount = $this->usages()->where('user_id', $user->id)->count();
        //     if ($userUsageCount >= $this->per_user_limit) {
        //         return false;
        //     }
        // }

        return true;
    }

    public function incrementUsage()
    {
        $this->increment('usage_count');
    }

    public function calculateDiscount($originalPrice)
    {
        switch ($this->discount_type) {
            case 'percentage':
                return ($originalPrice * $this->discount_value) / 100;
            case 'fixed_amount':
                return min($this->discount_value, $originalPrice);
            case 'free_trial':
                return $originalPrice; // Full discount for trial period
            default:
                return 0;
        }
    }
}
