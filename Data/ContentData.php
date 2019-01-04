<?php
/**
 * @license
 * Copyright 2019 TruongLuu. All Rights Reserved.
 */
namespace Truonglv\ContentAnalytics\Data;

use Truonglv\ContentAnalytics\App;

class ContentData
{
    public function getContentHandleTitlePhrases()
    {
        return App::contentDataRepo()->getContentHandleTitlePhrases();
    }
}
