<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */

namespace Truonglv\ContentAnalytics\Entity;

use XF\Mvc\Entity\Entity;
use XF\Mvc\Entity\Structure;

/**
 * COLUMNS
 * @property int|null data_id
 * @property string content_type
 * @property int content_id
 * @property int content_date
 * @property int count
 */
class AnalyticsData extends Entity
{
    public static function getStructure(Structure $structure)
    {
        $structure->table = 'xf_tl_content_analytics_data';
        $structure->primaryKey = 'data_id';
        $structure->shortName = 'Truonglv\ContentAnalytics:AnalyticsData';

        $structure->columns = [
            'data_id' => ['type' => self::UINT, 'nullable' => true, 'autoIncrement' => true],
            'content_type' => ['type' => self::STR, 'required' => true, 'maxLength' => 25],
            'content_id' => ['type' => self::UINT, 'required' => true],
            'content_date' => ['type' => self::UINT, 'required' => true],
            'count' => ['type' => self::UINT, 'forced' => true, 'default' => 0]
        ];

        return $structure;
    }
}
