<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics;

use Truonglv\ContentAnalytics\Repository\ContentData;

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
}
