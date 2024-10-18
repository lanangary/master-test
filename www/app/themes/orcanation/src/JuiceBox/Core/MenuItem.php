<?php

namespace JuiceBox\Core;

use Timber\MenuItem as TimberMenuItem;
use JuiceBox\Core\Post;

class MenuItem extends TimberMenuItem
{
    public $PostClass = Post::class;

    public $listItemClass = 'menu-item';

    public function __construct($data)
    {
        parent::__construct($data);

        // Add a modifier class if the item is the current page
        if ($data->current) {
            $this->add_class($this->listItemClass.'--current');
        }

        if ($data->current_item_parent) {
            $this->add_class($this->listItemClass.'--current '.$this->listItemClass.'--current-parent');
        }
    }
}