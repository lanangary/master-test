<?php
namespace JuiceBox\Core;

class DefaultContent {

    public function __construct()
    {
        // Create default content pages
        add_action('after_switch_theme', array($this, 'add_default_pages'));
    }

    public function add_default_pages()
    {
        $this->create_page_if_null('Home Page', '', 'publish', 'page--home-page.php');
        $this->create_page_if_null('About', '', 'publish', '');
        $this->create_page_if_null('Blog', '', 'publish', '');
        $this->create_page_if_null('Contact', '', 'publish', 'page--contact.php');
        $this->create_page_if_null('Grid');

        $dir = new \DirectoryIterator(get_stylesheet_directory() . '/pages');

        foreach ($dir as $dirinfo) {
            if (!$dirinfo->isDot()) {
                $this->create_page_if_null(
                    $dirinfo->getBasename(".{$dirinfo->getExtension()}"),
                    file_get_contents($dirinfo->getPathname())
                );
            }
        }

        //Set the homepage as the front page
        $homepage = get_page_by_title('Home Page');
        if($homepage){
            update_option('page_on_front', $homepage->ID);
            update_option('show_on_front', 'page');
        }

        //Set the posts page
        $blog = get_page_by_title('Blog');
        if($blog){
            update_option('page_for_posts', $blog->ID);
        }
    }

    public function create_page_if_null($title, $content = '', $status = 'draft', $template = null)
    {
        if (get_page_by_title($title) == null) {
            $page = array(
                'post_title' => $title,
                'post_content' => $content,
                'post_status' => $status,
                'post_author' => 1,
                'post_type' => 'page',
                'post_name' => strtolower(str_replace(' ', '-', $title)),
            );

            if ($template) {
                $page['page_template'] = $template;
            }

            wp_insert_post($page);
        }
    }
}
?>
