<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;

class AppraisalRequest extends Model
{
    protected $fillable = [
        'make',
        'model',
        'grade',
        'model_year',
        'mileage',
        'color',
        'condition',
        'name',
        'email',
        'phone',
        'zip',
        'message',
        'read_at',
    ];

    protected function casts(): array
    {
        return [
            'read_at' => 'datetime',
            'mileage' => 'integer',
            'model_year' => 'integer',
        ];
    }

    public function scopeUnread(Builder $query): Builder
    {
        return $query->whereNull('read_at');
    }

    public function getConditionLabelAttribute(): string
    {
        return match ($this->condition) {
            'good'    => '良好',
            'normal'  => '普通',
            'damaged' => '傷・凹みあり',
            default   => $this->condition,
        };
    }
}
