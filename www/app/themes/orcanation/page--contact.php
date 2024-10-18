<?php
/**
 *  Template Name:  Contact Page
 */

$context = Timber::get_context();

$post = new JuiceBox\Core\Post();

$context['post'] = $post;

Timber::render(['page--contact.twig', 'page.twig'], $context);
