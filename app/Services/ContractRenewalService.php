<?php

namespace App\Services;

/**
 * Contract expiry calculations and renewal period defaults.
 */
class ContractRenewalService
{
    public function daysUntilExpiry(?string $endDate): ?int
    {
        $endDate = trim((string) $endDate);
        if ($endDate === '') {
            return null;
        }

        $endTs = strtotime($endDate);
        if ($endTs === false) {
            return null;
        }

        $todayTs = strtotime(date('Y-m-d'));

        return (int) ceil(($endTs - ($todayTs ?: time())) / 86400);
    }

    /**
     * @return array{start_date: string, end_date: string, contract_date: string, duration_months: int}
     */
    public function renewalPeriodDefaults(?string $oldStart, ?string $oldEnd, int $fallbackMonths = 4): array
    {
        $today   = date('Y-m-d');
        $todayTs = strtotime($today) ?: time();

        $oldEnd   = trim((string) $oldEnd);
        $oldStart = trim((string) $oldStart);

        if ($oldEnd === '') {
            $newStartTs = strtotime('+1 day', $todayTs);
            $months     = max(1, $fallbackMonths);
            $newStart   = date('Y-m-d', $newStartTs ?: $todayTs);
            $newEndTs   = strtotime('+' . $months . ' months', $newStartTs ?: $todayTs);

            return [
                'start_date'      => $newStart,
                'end_date'        => $newEndTs ? date('Y-m-d', $newEndTs) : date('Y-m-d', strtotime('+' . $months . ' months', $todayTs)),
                'contract_date'   => $today,
                'duration_months' => $months,
            ];
        }

        $oldEndTs = strtotime($oldEnd);
        if ($oldEndTs === false) {
            return $this->renewalPeriodDefaults($oldStart, '', $fallbackMonths);
        }

        $newStartTs = strtotime('+1 day', $oldEndTs);
        if ($newStartTs === false || $newStartTs <= $todayTs) {
            $newStartTs = strtotime('+1 day', $todayTs);
        }

        $months = $this->durationMonths($oldStart, $oldEnd);
        if ($months < 1) {
            $months = max(1, $fallbackMonths);
        }

        $newStart = date('Y-m-d', $newStartTs ?: $todayTs);
        $newEndTs = strtotime('+' . $months . ' months', $newStartTs ?: $todayTs);
        $newEnd   = $newEndTs ? date('Y-m-d', $newEndTs) : date('Y-m-d', strtotime('+' . $months . ' months', $newStartTs ?: $todayTs));

        return [
            'start_date'      => $newStart,
            'end_date'        => $newEnd,
            'contract_date'   => $today,
            'duration_months' => max(1, $this->durationMonths($newStart, $newEnd) ?: $months),
        ];
    }

    public function durationMonths(string $start, string $end): int
    {
        if ($start === '' || $end === '') {
            return 0;
        }

        $s = strtotime($start);
        $e = strtotime($end);
        if (! $s || ! $e || $e < $s) {
            return 0;
        }

        return max(1, (int) round(($e - $s) / (30.437 * 86400)));
    }
}
