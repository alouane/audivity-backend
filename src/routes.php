<?php

// Routes
$app->post('/auth/login', Application\Controller\LoginController::class);
$app->get('/api/users', Application\Controller\UserApiController::class . ':index');
$app->get('/stream', Application\Controller\StreamFileController::class)->setOutputBuffering(false);
$app->get('/about', Application\Controller\HomeController::class . ':about');
// $app->get('/[{name}]', Application\Controller\HomeController::class . ':index');

//Register client blog post url
$app->post('/user/url', Application\Controller\RegisterController::class . ':anonyme_url');

//Register email & name of the client linked to a RequestID(blog post url ID)
$app->post('/user/register_profile', Application\Controller\RegisterController::class . ':register_profile');

//Get audio samples for a specific request ID
$app->get('/audio/sample_audios', Application\Controller\AudioController::class . ':sample_audios');

//Save user's preferd audio sample
$app->get('/audio/preferd_audio_sample', Application\Controller\AudioController::class . ':preferd_audio_sample');

//Save user's dissatisfaction
$app->post('/audio/dissatisfaction', Application\Controller\AudioController::class . ':dissatisfaction');

//Get audio
$app->get('/audio/get', Application\Controller\AudioController::class . ':get');

//User join
$app->get('/audio/join', Application\Controller\AudioController::class . ':join');