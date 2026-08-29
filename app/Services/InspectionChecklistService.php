<?php

namespace App\Services;

/**
 * Default checklist templates and schedule metadata for property & unit inspections.
 */
class InspectionChecklistService
{
    public const FREQUENCIES = [
        'weekly'  => 'Weekly',
        'monthly' => 'Monthly',
        'regular' => 'Regular / Routine',
    ];

    /** @return list<string> */
    public static function propertyTemplateItems(string $frequency): array
    {
        return match ($frequency) {
            'weekly' => [
                'Exterior grounds — litter, landscaping, parking',
                'Common corridors and lobbies — cleanliness',
                'Elevators — operation, alarms, emergency phone',
                'Stairwells — lighting, handrails, fire doors',
                'Fire exits — unlocked, signage visible, paths clear',
                'Restrooms (common) — stocked and clean',
                'Waste / garbage areas — no overflow',
                'Security — gates, CCTV, access control',
            ],
            'monthly' => [
                'HVAC plant room — filters, belts, leaks',
                'Electrical panels — labels, no exposed wiring',
                'Fire extinguishers — pressure, seal, location',
                'Fire alarm panel — fault log reviewed',
                'Generator — test run, fuel level',
                'Water tanks / pumps — pressure, leaks',
                'Pest control report reviewed',
                'Roof / terrace — drains, waterproofing',
                'Parking & basement — lighting, ventilation',
                'Swimming pool / gym (if applicable)',
            ],
            default => [
                'General building condition walkthrough',
                'Lighting in common areas',
                'Plumbing — visible leaks, water pressure',
                'Doors and locks — common access points',
                'Signage and wayfinding',
                'Housekeeping standards',
                'Health & safety hazards noted',
                'Tenant / occupant feedback logged',
            ],
        };
    }

    /** @return list<array{id: string, label: string}> */
    public static function unitTemplateItems(string $frequency): array
    {
        $labels = match ($frequency) {
            'weekly' => [
                'General cleanliness',
                'Kitchen / pantry condition',
                'Bathroom sanitary fittings',
                'A/C temperature and airflow',
                'Doors and windows operational',
                'Lights and switches working',
                'No visible leaks or damp',
                'Pest / odor check',
            ],
            'monthly' => [
                'Deep clean standard met',
                'A/C filters and vents',
                'Plumbing — taps, drains, WC',
                'Electrical — sockets, breakers',
                'Appliances (if fitted)',
                'Furniture / fixtures condition',
                'Keys and access cards',
                'Meter readings recorded',
            ],
            default => [
                'Cleanliness and housekeeping',
                'Walls, floors, ceilings',
                'Electrical fixtures',
                'Plumbing and water',
                'Air conditioning',
                'Doors, windows, locks',
                'Fire safety (extinguisher visible)',
                'Common area access',
            ],
        };

        $items = [];
        foreach ($labels as $i => $label) {
            $items[] = ['id' => 'item_' . $i, 'label' => $label];
        }

        return $items;
    }

    public static function frequencyLabel(?string $frequency): string
    {
        return self::FREQUENCIES[$frequency ?? ''] ?? ucfirst((string) $frequency);
    }

    public static function normalizeFrequency(?string $value): string
    {
        $v = strtolower(trim((string) $value));
        return in_array($v, ['weekly', 'monthly', 'regular'], true) ? $v : 'regular';
    }

    /** Map URL segment to unit checklist type + frequency */
    public static function resolveUnitChecklist(string $segment): array
    {
        return match ($segment) {
            'weekly'  => ['type' => 'routine', 'frequency' => 'weekly'],
            'monthly' => ['type' => 'routine', 'frequency' => 'monthly'],
            'routine', 'regular' => ['type' => 'routine', 'frequency' => 'regular'],
            'move_in' => ['type' => 'move_in', 'frequency' => 'regular'],
            'move_out' => ['type' => 'move_out', 'frequency' => 'regular'],
            'handover' => ['type' => 'handover', 'frequency' => 'regular'],
            default => ['type' => 'routine', 'frequency' => 'regular'],
        };
    }
}
