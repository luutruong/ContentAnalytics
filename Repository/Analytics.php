<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */

namespace Truonglv\ContentAnalytics\Repository;

use XF\Mvc\Entity\Repository;
use Truonglv\ContentAnalytics\App;

class Analytics extends Repository
{
    public function getAnalyticsData($contentType, $contentId, $grouping)
    {
        if (!is_array($contentType)) {
            $contentType = [$contentType];
        }

        $now = \XF::$time;
        if ($grouping === 'hourly') {
            return $this->getAnalyticsDataHourly($contentType, $contentId);
        }

        if ($grouping === 'daily') {
            $fromDate = $now - 30 * 86400;
            $toDate = $now;
            $groupUnit = '+1 days';
            $queryDateFormat = '%Y-%m-%d';
        } elseif ($grouping === 'monthly') {
            $fromDate = $now - 365 * 86400;
            $toDate = $now;
            $groupUnit = '+1 months';
            $queryDateFormat = '%Y-%m';
        } else {
            throw new \InvalidArgumentException('Unknown analytics grouping (' . $grouping . ')');
        }

        $db = $this->db();
        $records = $db->fetchAll('
            SELECT SUM(count) AS total, content_type, 
                FROM_UNIXTIME(content_date, "' . $queryDateFormat . '") AS content_date_formatted,
                MAX(content_date) AS content_date
            FROM xf_tl_content_analytics_data FORCE INDEX (content_type_id_date)
            WHERE content_type IN (' . $db->quote($contentType) . ')
                AND content_id = ?
                AND content_date BETWEEN ? AND ?
            GROUP BY content_type, content_date_formatted
            ORDER BY content_date
        ', [
            $contentId,
            $fromDate,
            $toDate
        ]);

        $results = [];
        $dt = new \DateTime('@' . $fromDate);
        $dt->setTime(0, 0, 0);
        if ($grouping === 'monthly') {
            $dt->setDate((int) $dt->format('Y'), (int) $dt->format('m'), 1);
        }

        $visitor = \XF::visitor();
        $language = $this->app()->language($visitor->language_id);
        $phpDateFormat = str_replace('%', '', $queryDateFormat);

        while (true) {
            $date = $dt->format($phpDateFormat);
            $ts = $dt->format('U');
            if ($ts >= $toDate) {
                break;
            }

            $resultItem = [
                'ts' => $ts,
                'label' => $language->date($ts),
                'averages' => []
            ];

            foreach ($contentType as $contentTypeItem) {
                $resultItem['averages'][$contentTypeItem] = 0;

                foreach ($records as $index => $record) {
                    if ($record['content_type'] === $contentTypeItem
                        && $record['content_date_formatted'] === $date
                    ) {
                        unset($records[$index]);
                        $resultItem['averages'][$contentTypeItem] += (int) $record['total'];

                        break;
                    }
                }
            }

            $results[$date] = $resultItem;
            $dt->modify($groupUnit);
        }

        return $results;
    }

    public function getDisplayTypes($contentTypes)
    {
        if (!is_array($contentTypes)) {
            $contentTypes = [$contentTypes];
        }

        $displayTypes = [];
        foreach ($contentTypes as $contentType) {
            $displayTypes[$contentType] = App::contentDataRepo()->getContentHandlerTitle($contentType);
        }

        return $displayTypes;
    }

    protected function getAnalyticsDataHourly(array $contentTypes, $contentId)
    {
        $now = \XF::$time;
        $contentDataRepo = App::contentDataRepo();

        $dataRaw = [];
        $fromDate = $now - 24 * 3600;
        $toDate = $now;

        foreach ($contentTypes as $contentTypeItem) {
            $dataRaw[$contentTypeItem] = $contentDataRepo
                ->getHandler($contentTypeItem)
                ->getAnalyticsDataHourly($contentId, $fromDate, $toDate);
        }

        $results = [];
        $fromDate = floor($fromDate/3600) * 3600;

        $visitor = \XF::visitor();
        $language = $this->app()->language($visitor->language_id);

        while ($fromDate <= $toDate) {
            $dataKey = date('Y-m-d H:i', intval($fromDate));

            $resultItem = [
                'ts' => $fromDate,
                'label' => $language->date($fromDate, 'H:i'),
                'averages' => []
            ];
            foreach ($contentTypes as $contentTypeItem) {
                if (isset($dataRaw[$contentTypeItem][$dataKey])) {
                    $resultItem['averages'][$contentTypeItem] = $dataRaw[$contentTypeItem][$dataKey]['total'];
                } else {
                    $resultItem['averages'][$contentTypeItem] = 0;
                }
            }

            $results[$dataKey] = $resultItem;
            $fromDate += 3600;
        }

        return $results;
    }
}
