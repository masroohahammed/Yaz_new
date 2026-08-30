<?php

namespace App\Services;

/**
 * Default inspection areas and issue metadata by entity scope.
 */
class InspectionAreaService
{
    /** @return list<string> */
    public static function propertyAreas(): array
    {
        return [
            'Inside',
            'Outside',
            'Frontside',
            'Backside',
            'Roof',
            'Basement',
            'Parking Area',
            'Garden / Landscaping',
            'Common Areas',
            'Entrance / Lobby',
            'Kitchen',
            'Bathrooms',
            'Bedrooms',
            'Living Areas',
            'Utility / Service Areas',
            'Other',
        ];
    }

    /** @return list<string> */
    public static function unitAreas(): array
    {
        return [
            'Kitchen',
            'Bathroom',
            'Living Room',
            'Bedroom',
            'Balcony / Terrace',
            'Entrance / Hallway',
            'Utility / Service Areas',
            'Other',
        ];
    }

    /** @return list<string> */
    public static function assetAreas(): array
    {
        return [
            'General Condition',
            'Safety / Compliance',
            'Operational Performance',
            'Maintenance / Wear',
            'Documentation / Labels',
            'Surrounding Area',
            'Other',
        ];
    }

    /** @return list<string> */
    public static function priorities(): array
    {
        return ['critical', 'urgent', 'medium', 'low'];
    }

    /** @return list<string> */
    public static function issueStatuses(): array
    {
        return ['open', 'in_progress', 'resolved', 'deferred', 'na'];
    }

    /** @return list<string> */
    public static function conditionRatings(): array
    {
        return ['excellent', 'good', 'fair', 'poor', 'damaged'];
    }

    /**
     * @return list<string>
     */
    public static function defaultAreasForScope(string $scopeType): array
    {
        return match ($scopeType) {
            'property' => self::propertyAreas(),
            'asset'    => self::assetAreas(),
            default    => self::unitAreas(),
        };
    }

    /**
     * Build pm-inspections create URL for QR scan routing.
     *
     * @param array{facility_id?: int, unit_id?: int, asset_id?: int, floor_label?: string} $params
     */
    public static function createUrl(array $params): string
    {
        $qs = [];
        if (! empty($params['asset_id'])) {
            $qs['scope']    = 'asset';
            $qs['asset_id'] = (int) $params['asset_id'];
        } elseif (! empty($params['unit_id'])) {
            $qs['scope']    = 'unit';
            $qs['unit_id']   = (int) $params['unit_id'];
            if (! empty($params['facility_id'])) {
                $qs['property_id'] = (int) $params['facility_id'];
            }
        } elseif (! empty($params['facility_id'])) {
            $qs['scope']       = 'property';
            $qs['property_id'] = (int) $params['facility_id'];
        }
        if (! empty($params['floor_label'])) {
            $qs['floor_label'] = (string) $params['floor_label'];
        }

        return function_exists('base_url')
            ? base_url('pm-inspections/create' . ($qs !== [] ? '?' . http_build_query($qs) : ''))
            : 'pm-inspections/create' . ($qs !== [] ? '?' . http_build_query($qs) : '');
    }
}
