<?php
/**
 *  Template Name:  Home Page
 */

$context = Timber::get_context();

$post = new JuiceBox\Core\Post();

$context['post'] = $post;

Timber::render(['page--home-page.twig', 'page.twig'], $context);
