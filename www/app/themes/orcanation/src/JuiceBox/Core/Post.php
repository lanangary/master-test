<?php

namespace JuiceBox\Core;

use Timber\Post as TimberPost;
use JuiceBox\Core\Image;
use JuiceBox\Core\Term;

class Post extends TimberPost
{
    use HasModuleLoop;

    public $PostClass = TimberPost::class;
    public $ImageClass = Image::class;
    public $TermClass = Term::class;
    public $modules = null;
    public $subnav = null;

    /**
     * @return PostPreview
     */
    public function preview()
    {
        return new PostPreview($this);
    }

    public function get_thumbnail($fallback = false)
    {
        $tid = get_post_thumbnail_id($this->ID);

        if ( $tid ) {
            return new $this->ImageClass((int) $tid);
        }

        if ($fallback) {
            $default = get_field($fallback, 'option');

            if (! empty($default) ) {

                if ($default instanceof $this->ImageClass) {
                    return $default;
                }

                return new $this->ImageClass($default);
            }
        }

        return null;
    }

    public function thumbnail($fallback = false)
    {
        return $this->get_thumbnail($fallback);
    }

    public function get_field($selector)
    {
        return get_field($selector, $this->ID);
    }

    public function get_fb_share_link()
    {
        return 'https://www.facebook.com/sharer/sharer.php?u='.$this->link;
    }

    public function get_twitter_share_link()
    {
        return 'https://twitter.com/share?text='.urlencode(html_entity_decode($this->title . " - ". get_bloginfo('name'), ENT_COMPAT, 'UTF-8'))."&amp;url=" . get_bloginfo('url') . "?p=" . $this->ID;
    }

    public function get_linkedin_share_link()
    {
        return 'https://www.linkedin.com/shareArticle?mini=true&url=' . $this->link .  '&amp;title=' . urlencode(html_entity_decode($this->title . " - ". get_bloginfo('name'), ENT_COMPAT, 'UTF-8'));
    }

    public function get_pinterest_share_link()
    {
        $thumbnail = $this->get_thumbnail(false);
        if(!empty($thumbnail)){
            return 'https://pinterest.com/pin/create/button/?url=' . $this->link . '&amp;media='.rawurlencode(html_entity_decode($thumbnail)).'&amp;description=' . rawurlencode(html_entity_decode($this->title));
        }
    }

    public function has_children() {
        $children = $this->children();
        return (is_array($children) && count($children) > 0);
    }

    /**
     * Gets the Yoast primary term for the post,
     * or the first one defined if an error.
     *
     * @return array|bool containing the following:
     *      'id' => the category id
     *      'display' => the category name
     *      'link' => link to the category page
     *      or FALSE if post has no category assigned.
     */
    public function get_primary_term($taxonomy = 'category')
    {
        $category = get_the_terms($this->ID, $taxonomy);
        if (!$category) {
            return false;
        }

        $return = array(
            'id' => $category[0]->term_id,
            'display' => $category[0]->name,
            'link' => get_term_link($category[0]->term_id, $taxonomy),
        );

        if (class_exists('\WPSEO_Primary_Term')) {
            // Show the post's 'Primary' category, if this Yoast feature is available, & one is set
            $wpseo_primary_term = new \WPSEO_Primary_Term($taxonomy, $this->ID);
            $wpseo_primary_term = $wpseo_primary_term->get_primary_term();
            $term = get_term($wpseo_primary_term);
            if (!is_wp_error($term)) {
                // Yoast Primary category
                $category_display = $term->name;
                $return['id'] = $term->term_id;
                $category_link = get_term_link($term->term_id, $taxonomy);
            }
        }

        // Return category data.
        if (!empty($category_display)) {
            if (!empty($category_link)) {
                $return['link'] = $category_link;
            }

            $return['display'] = htmlspecialchars($category_display);
        }

        return $return;
    }

    public function get_primary_category() {
        return $this->get_primary_term('category');
    }

    public function parent_category()
    {
        $terms = $this->terms('category', $this->TermClass);

        foreach ( $terms as $term ) {
            if ( $term->parent == 0 ) {
                return $term->title();
            }
        }
    }

    public function get_term_links( $taxonomy = 'category' )
    {
        $terms = $this->terms($taxonomy, $this->TermClass);
        $default = get_option('default_category');

        $return = [];

        foreach ( $terms as $term ) {
            if ( $term->id == $default ) {
                continue;
            }
            $return[] = $term->name;
        }

        return implode(', ', $return);
    }

    public function get_share_link()
    {
        return [
            [
                'title' => 'Facebook',
                'icon'  => 'facebook',
                'link'  => $this->get_fb_share_link()
            ],
            [
                'title' => 'Twitter',
                'icon'  => 'twitter',
                'link'  => $this->get_twitter_share_link()
            ],
            [
                'title' => 'Share on Pinterest',
                'type' => 'pinterest',
                'icon'  => 'pinterest',
                'link'  => $this->get_pinterest_share_link()
            ],
        ];
    }

    public function get_next_posts( $limit = 3 )
    {
        $posts = [];
        $posts[] = $this->prev();

        if( $posts[0] == false ) {
            return;
        }

        for ( $a = 1 ; $a < $limit ; $a++ ) {
            $posts[$a] = $posts[$a-1]->prev();

            if ( $posts[$a] === false ) {
                unset($posts[$a]);
                return $posts;
            }
        }

        return $posts;
    }
}
