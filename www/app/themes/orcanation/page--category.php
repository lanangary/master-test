<?php
/**
 *  Template Name:  Category Page
 */

$context = Timber::get_context();

$post = new JuiceBox\Core\Post();

$context['post'] = $post;

Timber::render(['page--category.twig', 'page.twig'], $context);
