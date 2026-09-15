<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Exam extends Model
{
    use HasFactory;

    protected $fillable = [
        'school_id',
        'school_class_id',
        'term_id',
        'name',
        'term',
        'year',
        'exam_date',
        'status',
        'submitted_by',
        'submitted_at',
        'approved_by',
        'approved_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'exam_date' => 'date',
            'submitted_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    public function school()
    {
        return $this->belongsTo(School::class);
    }

    public function schoolClass()
    {
        return $this->belongsTo(SchoolClass::class, 'school_class_id');
    }

    /**
     * Named academicTerm (not term()) deliberately - this model already has a
     * legacy raw 'term' string column from before Terms existed as an entity.
     * A method named term() would silently never fire via $exam->term, since
     * Eloquent always prefers a real attribute/column over a same-named relation.
     */
    public function academicTerm()
    {
        return $this->belongsTo(Term::class, 'term_id');
    }

    public function marks()
    {
        return $this->hasMany(Mark::class);
    }

    public function submittedBy()
    {
        return $this->belongsTo(User::class, 'submitted_by');
    }

    public function approvedBy()
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    // --- Workflow helpers (teacher submits -> principal approves/rejects) ---

    public function submit(User $teacher): void
    {
        $this->update([
            'status' => 'submitted',
            'submitted_by' => $teacher->id,
            'submitted_at' => now(),
        ]);
    }

    public function approve(User $principal): void
    {
        $this->update([
            'status' => 'approved',
            'approved_by' => $principal->id,
            'approved_at' => now(),
            'rejection_reason' => null,
        ]);
    }

    public function reject(User $principal, string $reason): void
    {
        $this->update([
            'status' => 'rejected',
            'approved_by' => $principal->id,
            'approved_at' => now(),
            'rejection_reason' => $reason,
        ]);
    }
}
