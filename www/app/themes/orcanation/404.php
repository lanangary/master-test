<?php

$context = Timber::get_context();
$post = new JuiceBox\Core\Post();
$post->title = 'Page not found';
$post->init_post();


$post->internal_title = 'Page not found';
$post->internal_supporting_text = 'Sorry, we couldn\'t find what you\'re looking for.';

$context['post'] = $post;

Timber::render('404.twig', $context);
