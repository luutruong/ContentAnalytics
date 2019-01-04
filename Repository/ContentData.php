<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */

namespace Truonglv\ContentAnalytics\Repository;

use XF\Timer;
use XF\Mvc\Entity\Repository;
use Truonglv\ContentAnalytics\Entity\AnalyticsData;
use Truonglv\ContentAnalytics\ContentData\AbstractHandler;

class ContentData extends Repository
{
    public function getContentHandlers()
    {
        return [
            // [content_type] => [handler class]
            'forum_thread' => 'Truonglv\ContentAnalytics\ContentData\ForumThread',
            'forum_post' => 'Truonglv\ContentAnalytics\ContentData\ForumPost',
        ];
    }

    public function getContentHandleTitlePhrases()
    {
        $titlePhrases = [];

        foreach ($this->getContentHandlers() as $contentType => $handler) {
            $titlePhrases[$contentType] = $this->getContentHandlerTitle($contentType);
        }

        return $titlePhrases;
    }

    public function getContentHandlerTitle($contentType)
    {
        $handler = $this->getHandler($contentType);

        return $handler->getContentTitlePhrase() ?: $contentType;
    }

    public function processData($contentType, $maxRunTime)
    {
        $handlerObj = $this->getHandler($contentType);

        /** @var AnalyticsData|null $lastProceed */
        $lastProceed = $this->finder('Truonglv\ContentAnalytics:AnalyticsData')
            ->where('content_type', $contentType)
            ->order('content_date', 'DESC')
            ->fetchOne();
        $lastProceedDate = $lastProceed ? $lastProceed->content_date : 0;
        $timer = $maxRunTime ? new Timer($maxRunTime) : null;

        $contentStats = $handlerObj->getContentStatsInRange($lastProceedDate, \XF::$time);
        foreach ($contentStats as $data) {
            $contentDate = floor($data['content_date']/3600) * 3600;

            $this->createAnalyticsDataRecord(
                $contentType,
                $data['content_id'],
                $contentDate,
                $data['total']
            );

            if ($timer && $timer->limitExceeded()) {
                break;
            }
        }
    }

    public function createAnalyticsDataRecord($contentType, $contentId, $contentDate, $count)
    {
        /** @var AnalyticsData $data */
        $data = $this->em->create('Truonglv\ContentAnalytics:AnalyticsData');

        $data->content_type = $contentType;
        $data->content_id = $contentId;
        $data->content_date = $contentDate;
        $data->count = $count;

        return $data->save();
    }

    /**
     * @param string $contentType
     * @return AbstractHandler
     */
    public function getHandler($contentType)
    {
        $contentHandlers = $this->getContentHandlers();
        if (!isset($contentHandlers[$contentType])) {
            throw new \InvalidArgumentException('Unknown handler for content type (' . $contentType . ')');
        }

        /** @var AbstractHandler $handlerObj */
        $handlerObj = $this->app()->container()->createObject($this->getContentHandlers()[$contentType]);
        if (!$handlerObj instanceof AbstractHandler) {
            throw new \InvalidArgumentException(sprintf(
                'Handler (%s) must be instance of [Truonglv\ContentAnalytics\ContentData\AbstractHandler]',
                $contentHandlers[$contentType]
            ));
        }

        return $handlerObj;
    }
}
