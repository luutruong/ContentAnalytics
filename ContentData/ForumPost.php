<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */

namespace Truonglv\ContentAnalytics\ContentData;

class ForumPost extends AbstractHandler
{
    public function getContentTitlePhrase()
    {
        return \XF::phrase('posts');
    }

    public function getContentStatsInRange($fromDate, $toDate)
    {
        return $this->getStatsBasicQuery(
            'xf_post',
            'thread.node_id',
            'post_date',
            'thread.node_id > ? AND base_table.position > ?',
            [$fromDate, $toDate, 0, 0]
        );
    }

    public function getAnalyticsDataHourly($contentId, $fromDate, $toDate)
    {
        return $this->db()->fetchAllKeyed('
            SELECT COUNT(*) as total, FROM_UNIXTIME(FLOOR(MAX(post.post_date)/3600) * 3600, "%Y-%m-%d %H:%i") AS content_date
            FROM xf_post AS post
                INNER JOIN xf_thread AS thread ON (thread.thread_id = post.thread_id)
            WHERE thread.node_id = ? AND post.post_date BETWEEN ? AND ?
                AND post.position > ?
            GROUP BY FLOOR(post.post_date/3600)
            ORDER BY post.post_date
        ', 'content_date', [$contentId, $fromDate, $toDate, 0]);
    }

    protected function getBasicQueryJoinTables()
    {
        return 'INNER JOIN xf_thread AS thread ON (thread.thread_id = base_table.thread_id)';
    }
}
