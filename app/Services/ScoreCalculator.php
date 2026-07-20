<?php

namespace App\Services;

use App\Models\Result;

class ScoreCalculator
{
    /**
     * Calculate total score from results collection.
     * Each result's score = item.bobot - (interval * index_of_criterion).
     */
    public static function calculateFromResults($results): float
    {
        $total = 0;

        foreach ($results as $result) {
            $item = $result->item;
            if (!$item || !$item->bobot) continue;

            $criteriaCount = $item->criteria->count();
            if ($criteriaCount <= 1) continue;

            $interval = $item->bobot / ($criteriaCount - 1);
            $idx = $item->criteria->search(fn($c) => $c->id === $result->criterion_id);

            if ($idx !== false) {
                $total += $item->bobot - ($interval * $idx);
            }
        }

        return $total;
    }

    /**
     * Calculate score for a report, using cached nilai if available.
     */
    public static function calculateForReport($report): float
    {
        if ($report->nilai !== null) {
            return (float) $report->nilai;
        }

        return self::calculateFromResults($report->results);
    }
}
