<?php

namespace App\Http\Requests\Principal;

use App\Models\ClassSubjectTeacher;
use App\Models\SchoolClass;
use App\Models\Timetable;
use Illuminate\Contracts\Validation\Validator as ValidatorContract;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Http\Exceptions\HttpResponseException;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class StoreTimetableSlotRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()?->isPrincipal() ?? false;
    }

    protected function failedValidation(ValidatorContract $validator)
    {
        throw new HttpResponseException(
            redirect()->back()
                ->withInput()
                ->withErrors($validator)
                ->with('active_day', $this->input('day_of_week'))
        );
    }

    public function rules(): array
    {
        $schoolId = $this->user()->school_id;

        return [
            'assignment_id' => [
                'required',
                // Rule::exists()->where() receives a plain query builder for the
                // 'class_subject_teacher' table - it has no whereHas(), so we scope
                // it with a subquery of this school's class IDs instead.
                Rule::exists('class_subject_teacher', 'id')->where(function ($q) use ($schoolId) {
                    $q->whereIn('school_class_id', SchoolClass::where('school_id', $schoolId)->pluck('id'));
                }),
            ],
            'day_of_week' => ['required', 'in:Monday,Tuesday,Wednesday,Thursday,Friday,Saturday,Sunday'],
            'start_time' => ['required', 'date_format:H:i'],
            'end_time' => ['required', 'date_format:H:i', 'after:start_time'],
            'room' => ['nullable', 'string', 'max:50'],
            'term_id' => ['nullable', Rule::exists('terms', 'id')->where('school_id', $schoolId)],
        ];
    }

    /**
     * Prevent double-booking the same teacher into two places at once on the same
     * day, and prevent a class from being scheduled into two subjects at once -
     * unless every subject involved is an elective (different students split off
     * to different rooms), in which case a room is required so it's clear where
     * each group actually goes.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function ($validator) {
            $assignment = ClassSubjectTeacher::with('subject')->find($this->input('assignment_id'));

            if (! $assignment || ! $this->filled(['day_of_week', 'start_time', 'end_time'])) {
                return;
            }

            $teacherClash = Timetable::where('teacher_id', $assignment->teacher_id)
                ->where('day_of_week', $this->input('day_of_week'))
                ->where('start_time', '<', $this->input('end_time'))
                ->where('end_time', '>', $this->input('start_time'))
                ->exists();

            if ($teacherClash) {
                $validator->errors()->add('start_time', 'This teacher already has another class scheduled during this time.');
            }

            $overlappingClassSlots = Timetable::where('school_class_id', $assignment->school_class_id)
                ->where('day_of_week', $this->input('day_of_week'))
                ->where('start_time', '<', $this->input('end_time'))
                ->where('end_time', '>', $this->input('start_time'))
                ->with('subject')
                ->get();

            if ($overlappingClassSlots->isEmpty()) {
                return;
            }

            $allElective = ! $assignment->subject->is_core
                && $overlappingClassSlots->every(fn ($slot) => ! $slot->subject->is_core);

            if (! $allElective) {
                $validator->errors()->add(
                    'assignment_id',
                    'This class already has '.($overlappingClassSlots->first()->subject->is_core ? 'a core subject' : 'another subject').' scheduled at this time. Two subjects can only overlap if both are electives.'
                );

                return;
            }

            // All electives overlapping at the same time - fine, but only if rooms
            // make it clear which group of students goes where.
            if (! $this->filled('room')) {
                $validator->errors()->add('room', 'These are overlapping electives - enter a room so it\'s clear where students taking this one should go.');
            } elseif ($overlappingClassSlots->contains(fn ($slot) => $slot->room === $this->input('room'))) {
                $validator->errors()->add('room', 'Another elective at this same time is already using that room - pick a different one.');
            }
        });
    }
}
