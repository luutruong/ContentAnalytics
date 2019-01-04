<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics;

use XF\Template\Templater;

class Callback
{
    public static function renderForumAnalytics($_, array $params, Templater $templater)
    {
        if (empty($params['forum']) || !App::hasPermission('viewForumAnalytics')) {
            return null;
        }

        $forum = $params['forum'];
        $request = \XF::app()->request();

        $grouping = $request->filter('grouping', 'str');
        if (!in_array($grouping, ['hourly', 'daily', 'monthly'], true)) {
            $grouping = 'hourly';
        }

        $contentTypes = ['forum_thread', 'forum_post'];
        $analyticsData = App::analyticsRepo()->getAnalyticsData(
            $contentTypes,
            $forum->node_id, $grouping
        );

        return $templater->renderTemplate('public:content_analytics_graph', [
            'data' => $analyticsData,
            'displayTypes' => App::analyticsRepo()->getDisplayTypes($contentTypes),
            'baseLink' => 'forums',
            'linkData' => $forum,
            'selected' => $grouping
        ]);
    }
}
