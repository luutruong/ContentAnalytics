<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */

namespace Truonglv\ContentAnalytics\ContentData;

abstract class AbstractHandler
{
    abstract public function getContentStatsInRange($fromDate, $toDate);
    abstract public function getAnalyticsDataHourly($contentId, $fromDate, $toDate);

    public function getContentTitlePhrase()
    {
        return null;
    }

    protected function getStatsBasicQuery($table, $contentIdColumn, $dateColumn, $extraWhere, $bind)
    {
        $db = $this->db();
        if (empty($extraWhere)) {
            $extraWhere = '1=1';
        }
        if (strpos($contentIdColumn, '.') === false) {
            $contentIdColumn = 'base_table.' . $contentIdColumn;
        }

        return $db->fetchAll("
            SELECT COUNT(*) AS total, MAX(base_table.{$dateColumn}) AS content_date,
                {$contentIdColumn} AS content_id
            FROM {$table} AS base_table
                {$this->getBasicQueryJoinTables()}
            WHERE base_table.{$dateColumn} >= ? AND base_table.{$dateColumn} < ?
                AND {$extraWhere}
            GROUP BY {$contentIdColumn}, FLOOR(base_table.{$dateColumn}/86400)
            ORDER BY base_table.{$dateColumn}
        ", $bind);
    }

    protected function db()
    {
        return \XF::app()->db();
    }

    protected function getBasicQueryJoinTables()
    {
        return '';
    }
}
