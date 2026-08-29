<?php

namespace App\Support;

/**
 * Allowed values for expenses.category (existing FM enum + landlord/property ops).
 * Do not invent a second expenses table — this only labels and groups the live column.
 */
final class PmExpenseCategories
{
    /**
     * Full ENUM list. Existing production values stay first.
     *
     * @return list<string>
     */
    public static function enumValues(): array
    {
        return [
            'labor', 'spare_parts', 'vendor', 'utility', 'administrative', 'emergency', 'other',
            'insurance', 'municipality', 'cleaning', 'security', 'management_fee',
            'maintenance', 'utilities', 'repairs', 'staff', 'admin', 'tax', 'renovation',
        ];
    }

    /** @return array<string, string> */
    public static function labels(): array
    {
        return [
            'labor'            => 'Labor',
            'spare_parts'      => 'Spare parts',
            'vendor'           => 'Vendor',
            'utility'          => 'Utility',
            'administrative'   => 'Administrative',
            'emergency'        => 'Emergency',
            'other'            => 'Other',
            'insurance'        => 'Insurance',
            'municipality'     => 'Municipality',
            'cleaning'         => 'Cleaning',
            'security'         => 'Security',
            'management_fee'   => 'Management fee',
            'maintenance'      => 'Maintenance',
            'utilities'        => 'Utilities',
            'repairs'          => 'Repairs',
            'staff'            => 'Staff',
            'admin'            => 'Admin',
            'tax'              => 'Tax',
            'renovation'       => 'Renovation',
        ];
    }

    public static function normalize(?string $raw): string
    {
        $cat = strtolower(trim((string) $raw));
        if ($cat === '' || ! in_array($cat, self::enumValues(), true)) {
            return 'other';
        }

        return $cat;
    }

    public static function groupKey(string $cat): string
    {
        $cat = strtolower($cat);

        return match (true) {
            in_array($cat, ['labor', 'spare_parts', 'vendor', 'emergency', 'maintenance', 'repairs'], true) => 'maintenance',
            in_array($cat, ['utility', 'utilities'], true) => 'utilities',
            $cat === 'insurance' => 'insurance',
            in_array($cat, ['municipality', 'tax'], true) => 'municipality',
            $cat === 'cleaning' => 'cleaning',
            $cat === 'security' => 'security',
            in_array($cat, ['management_fee', 'administrative', 'admin'], true) => 'management',
            default => 'other',
        };
    }

    public static function sqlEnum(): string
    {
        return implode(',', array_map(static fn ($v) => "'" . $v . "'", self::enumValues()));
    }
}
