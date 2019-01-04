<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics\ContentData;

class ForumPost extends AbstractHandler
{
    public function getContentStatsInRange($fromDate, $toDate)
    {
        return $this->getStatsBasicQuery(
            'xf_post',
            'thread.node_id',
            'post_date',
            'thread.node_id > ?',
            [$fromDate, $toDate, 0]
        );
    }

    protected function getBasicQueryJoinTables()
    {
        return 'INNER JOIN xf_thread AS thread ON (thread.thread_id = base_table.thread_id)';
    }
}
