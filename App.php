<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics;

use Truonglv\ContentAnalytics\Repository\Analytics;
use Truonglv\ContentAnalytics\Repository\ContentData;
use XF\Entity\User;

class App
{
    /**
     * @return ContentData
     */
    public static function contentDataRepo()
    {
        /** @var ContentData $repo */
        $repo = \XF::app()->repository('Truonglv\ContentAnalytics:ContentData');

        return $repo;
    }

    /**
     * @return Analytics
     */
    public static function analyticsRepo()
    {
        /** @var Analytics $repo */
        $repo = \XF::app()->repository('Truonglv\ContentAnalytics:Analytics');

        return $repo;
    }

    public static function hasPermission($permission, User $user = null)
    {
        $user = $user ?: \XF::visitor();
        return $user->hasPermission('general', 'tlCA_' . $permission);
    }
}
