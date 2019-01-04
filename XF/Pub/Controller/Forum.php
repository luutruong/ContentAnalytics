<?php

namespace Truonglv\ContentAnalytics\XF\Pub\Controller;

use Truonglv\ContentAnalytics\App;
use XF\Mvc\ParameterBag;
use XF\Mvc\Reply\View;

class Forum extends XFCP_Forum
{
    public function actionForum(ParameterBag $params)
    {
        $response = parent::actionForum($params);
        if ($response instanceof View
            && App::hasPermission('viewForumAnalytics')
        ) {
            /** @var \XF\Entity\Forum $forum */
            $forum = $response->getParam('forum');

            $analyticsData = App::analyticsRepo()->getAnalyticsData(
                ['forum_thread', 'forum_post'],
                $forum->node_id, 'hourly'
            );
        }

        return $response;
    }
}
