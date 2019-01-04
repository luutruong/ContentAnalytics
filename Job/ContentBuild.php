<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics\Job;

use Truonglv\ContentAnalytics\App;
use XF\Job\AbstractJob;

class ContentBuild extends AbstractJob
{
    public function canTriggerByChoice()
    {
        return true;
    }

    public function canCancel()
    {
        return false;
    }

    public function run($maxRunTime)
    {
        if (empty($this->data['content_type'])) {
            return $this->complete();
        }

        $contentType = $this->data['content_type'];

        $contentDataRepo = App::contentDataRepo();
        $contentDataRepo->processData($contentType, $maxRunTime);

        return $this->complete();
    }

    public function getStatusMessage()
    {
        return '';
    }
}
