<?php

namespace App\Support;

class GradeCalculator
{
    /**
     * Simple percentage-based grading. Adjust these bands per your school's
     * actual grading policy if it differs (e.g. CBC vs 8-4-4 style scales).
     */
    public static function forScore(null|int|float|string $score): ?string
    {
        if ($score === null || $score === '') {
            return null;
        }

        $score = (float) $score;

        return match (true) {
            $score >= 80 => 'A',
            $score >= 70 => 'B+',
            $score >= 60 => 'B',
            $score >= 50 => 'C+',
            $score >= 40 => 'C',
            $score >= 30 => 'D',
            default => 'E',
        };
    }
}
