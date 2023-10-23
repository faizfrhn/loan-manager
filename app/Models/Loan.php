<?php

namespace App\Models;

use App\Casts\MoneyCast;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Loan extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'amount_applied',
        'term',
        'status'
    ];

    protected $casts = [
        'amount_applied' => MoneyCast::class,
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function repaymentHistory(): HasMany
    {
        return $this->hasMany(WeeklyRepayment::class)->whereIn('status', ['paid']);
    }

    public function upcomingRepayments(): HasMany
    {
        return $this->hasMany(WeeklyRepayment::class)->whereNotIn('status', ['paid']);
    }
}
