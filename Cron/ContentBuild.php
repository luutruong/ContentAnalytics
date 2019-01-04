<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics\Cron;

use Truonglv\ContentAnalytics\App;

class ContentBuild
{
    public static function triggerJob()
    {
        $contentDataRepo = App::contentDataRepo();
        $jobManager = \XF::app()->jobManager();

        foreach ($contentDataRepo->getContentHandlers() as $contentType => $handler) {
            $jobManager->enqueueUnique(
                'tl_ContentAnalytics_' . substr(md5($contentType . $handler), 0, 8),
                'Truonglv\ContentAnalytics:ContentBuild',
                ['content_type' => $contentType]
            );
        }
    }
}
