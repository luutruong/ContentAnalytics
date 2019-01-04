<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics\ContentData;

class ForumThread extends AbstractHandler
{
    public function getContentTitlePhrase()
    {
        return \XF::phrase('threads');
    }

    public function getContentStatsInRange($fromDate, $toDate)
    {
        return $this->getStatsBasicQuery(
            'xf_thread',
            'node_id',
            'post_date',
            'base_table.node_id > ?',
            [$fromDate, $toDate, 0]
        );
    }

    public function getAnalyticsDataHourly($contentId, $fromDate, $toDate)
    {
        return $this->db()->fetchAllKeyed('
            SELECT COUNT(*) as total, FROM_UNIXTIME(FLOOR(MAX(post_date)/3600) * 3600, "%Y-%m-%d %H:%i") AS content_date
            FROM xf_thread
            WHERE node_id = ? AND post_date BETWEEN ? AND ?
            GROUP BY FLOOR(post_date/3600)
            ORDER BY post_date
        ', 'content_date', [$contentId, $fromDate, $toDate]);
    }
}
