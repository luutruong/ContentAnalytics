<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics\ContentData;

class ForumThread extends AbstractHandler
{
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
}
