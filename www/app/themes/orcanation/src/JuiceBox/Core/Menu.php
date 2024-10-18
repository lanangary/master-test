<?php

namespace JuiceBox\Core;

use \Timber\Menu as TimberMenu;
use JuiceBox\Core\MenuItem;
use JuiceBox\Core\Post;

class Menu extends TimberMenu
{
    public $MenuItemClass = MenuItem::class;
    public $PostClass = Post::class;
}
