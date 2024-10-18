<?php
/**
 * The template for displaying all pages.
 *
 */

$context = Timber::get_context();

$post = new JuiceBox\Core\Post();

$context['post'] = $post;

Timber::render(["page--{$post->post_name}.twig", "page.twig"], $context);
