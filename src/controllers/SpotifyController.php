<?php

// https://github.com/jwilsson/spotify-web-api-php
namespace clarknelson\spotify\controllers;

use clarknelson\spotify\Plugin as CraftSpotify;

use clarknelson\spotify\services\SpotifyService;

use Craft;
use Yii;
use app\models\Post;
use craft\web\Controller;

class SpotifyController extends Controller
{
    // https://clarknelson.com.ddev.site/actions/craft-spotify/spotify/auth
    public function actionAuth()
    {
        $service = CraftSpotify::getInstance()->spotify;
        $session = Craft::$app->getSession();
        
        $state = $service->session->generateState();
        $session->set('storedState', $state);
        $options = $service->getAuthOptions($state);
        return $this->redirect($service->session->getAuthorizeUrl($options));
    }

    // https://clarknelson.com.ddev.site/actions/craft-spotify/spotify/callback
    public function actionCallback()
    {
        $service = CraftSpotify::getInstance()->spotify;
        $session = Craft::$app->getSession();

        $state = $_GET['state'];
        $storedState = $session->get('storedState');
        if ($state !== $storedState) {
            throw new \Exception('State mismatch');
        }

        // Request a access token using the code from Spotify
        $service->session->requestAccessToken($_GET['code']);

        $service->accessToken = $service->session->getAccessToken();
        $service->refreshToken = $service->session->getRefreshToken();

        CraftSpotify::getInstance()->settings->accessToken = $service->accessToken;
        CraftSpotify::getInstance()->settings->refreshToken = $service->accessToken;

        return $this->redirect('/');
    }

    // https://clarknelson.com.ddev.site/actions/craft-spotify/spotify/testing
    public function actionTesting()
    {
        $me = CraftSpotify::getInstance()->spotify->getMe();
        dd($me);
        return 0;
    }
}
