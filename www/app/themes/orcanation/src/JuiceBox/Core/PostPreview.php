<?php

namespace JuiceBox\Core;

use Timber\PostPreview as TimberPostPreview;

class PostPreview extends TimberPostPreview
{
    protected $end = '&hellip;';
    protected $force = false;
    protected $length = 20;
    protected $readmore = '';
    protected $strip = true;
}
