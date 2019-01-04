<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics\Job;

use Truonglv\ContentAnalytics\App;
use Truonglv\ContentAnalytics\Entity\AnalyticsData;
use XF\Job\AbstractJob;
use XF\Timer;

class ContentRebuild extends AbstractJob
{
    protected $defaultData = [
        'content_type' => '',
        'truncate' => false,
        'processing' => '',
        'completed' => []
    ];

    public function canCancel()
    {
        return true;
    }

    public function canTriggerByChoice()
    {
        return true;
    }

    public function run($maxRunTime)
    {
        $db = $this->app->db();
        if ($this->data['truncate']) {
            if (empty($this->data['content_type'])) {
                $db->emptyTable('xf_tl_content_graph_data');
            } else {
                $db->delete('xf_tl_content_graph_data', 'content_type = ?', $this->data['content_type']);
            }

            $this->data['truncate'] = false;
        }

        $contentDataRepo = App::contentDataRepo();
        $contentHandlers = $contentDataRepo->getContentHandlers();

        if (!empty($this->data['content_type'])) {
            // run specific content type.
            if ($this->hasCompletedRebuiltContent($this->data['content_type'])) {
                return $this->complete();
            }
            $this->data['processing'] = $this->data['content_type'];
        } else {
            // run all content types.
            $contentTypeKeys = array_keys($contentHandlers);
            $runByContent = null;

            while (count($contentTypeKeys) > 0) {
                $contentTypeForRun = array_shift($contentTypeKeys);
                if (!$this->hasCompletedRebuiltContent($contentTypeForRun)) {
                    $runByContent = $contentTypeForRun;
                    break;
                }
            }

            if (!$runByContent) {
                // all has completed.
                return $this->complete();
            }

            $this->data['processing'] = $runByContent;
        }

        $processingContentType = $this->data['processing'];
        $lastRunDate = 0;

        /** @var AnalyticsData|null $latestData */
        $latestData = $this->app->finder('Truonglv\ContentAnalytics:AnalyticsData')
            ->where('content_type', $processingContentType)
            ->order('content_date', 'DESC')
            ->fetchOne();
        if ($latestData) {
            $lastRunDate = $latestData->content_date;
        }

        $handler = $contentDataRepo->getHandler($processingContentType);
        $stepDate = 7 * 86400; // 7 days.
        $timer = new Timer($maxRunTime);

        while ($lastRunDate <= \XF::$time) {
            $contentStats = $handler->getContentStatsInRange($lastRunDate, $lastRunDate + $stepDate);
            foreach ($contentStats as $data) {
                $contentDate = floor($data['content_date']/3600) * 3600;

                $contentDataRepo->createAnalyticsDataRecord(
                    $processingContentType,
                    $data['content_id'],
                    $contentDate,
                    $data['total']
                );
            }
            $lastRunDate += $stepDate;

            if ($timer->limitExceeded()) {
                break;
            }
        }

        if (($lastRunDate + $stepDate) >= \XF::$time) {
            // it's completed.
            $this->data['completed'][] = $processingContentType;
        }

        if (count($contentHandlers) !== count($this->data['completed'])) {
            return $this->resume();
        }

        return $this->complete();
    }

    public function getStatusMessage()
    {
        $contentType = $this->data['content_type'];
        return sprintf('%s (%s)',
            \XF::phrase('content_analytics_rebuild_content_data'),
            $contentType ? App::contentDataRepo()->getContentHandlerTitle($contentType) : ''
        );
    }

    protected function hasCompletedRebuiltContent($contentType)
    {
        return in_array($contentType, $this->data['completed'], true);
    }
}
