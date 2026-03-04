<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ChartOfAccount extends Model
{
    protected $table = 'chart_of_accounts';

    protected $fillable = [
        'user_id',
        'code',
        'name',
        'type',
        'nature',
        'category',
        'parent_id',
        'opening_balance',
        'is_editable',
        'is_deletable',
        'allows_children',
        'is_active',
    ];

    protected $casts = [
        'opening_balance' => 'decimal:2',
        'is_active' => 'boolean',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function journalLines(): HasMany
    {
        return $this->hasMany(JournalEntryLine::class, 'chart_of_account_id');
    }

    public function parent()
    {
        return $this->belongsTo(ChartOfAccount::class, 'parent_id');
    }
}
