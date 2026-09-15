<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class School extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'code',
        'county',
        'sub_county',
        'address',
        'phone',
        'email',
        'logo_path',
        'status',
        'created_by',
    ];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function principals()
    {
        return $this->users()->where('role', 'principal');
    }

    public function teachers()
    {
        return $this->users()->where('role', 'teacher');
    }

    public function classes()
    {
        return $this->hasMany(SchoolClass::class);
    }

    public function subjects()
    {
        return $this->hasMany(Subject::class);
    }

    public function students()
    {
        return $this->hasMany(Student::class);
    }

    public function exams()
    {
        return $this->hasMany(Exam::class);
    }

    public function feeTransactions()
    {
        return $this->hasMany(FeeTransaction::class);
    }

    public function notices()
    {
        return $this->hasMany(Notice::class);
    }
}
