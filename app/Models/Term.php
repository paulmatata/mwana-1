<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Term extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'name',
        'year',
        'start_date',
        'end_date',
        'is_current',
    ];

    protected function casts(): array
    {
        return [
            'start_date' => 'date',
            'end_date' => 'date',
            'is_current' => 'boolean',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function timetables()
    {
        return $this->hasMany(Timetable::class);
    }

    public function feeTransactions()
    {
        return $this->hasMany(FeeTransaction::class);
    }

    public function label(): string
    {
        return "{$this->name} {$this->year}";
    }

    /**
     * Marks this term as current for its school, unmarking any other current
     * term at the same school in the same step.
     */
    public function makeCurrent(): void
    {
        static::where('school_id', $this->school_id)->where('id', '!=', $this->id)->update(['is_current' => false]);
        $this->update(['is_current' => true]);
    }
}
