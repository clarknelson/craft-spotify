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
        $state = $service->session->generateState();
        $options = $service->getAuthOptions($state);
        // dump($service->session->getAuthorizeUrl($options));
        // return 0;
        return $this->redirect($service->session->getAuthorizeUrl($options));
    }

    // https://clarknelson.com.ddev.site/actions/craft-spotify/spotify/callback
    public function actionCallback()
    {
        $service = CraftSpotify::getInstance()->spotify;
        $session = Craft::$app->getSession();

        if (isset($_GET['code'])) {
            // Request a access token using the code from Spotify
            $service->session->requestAccessToken($_GET['code']);

            $service->accessToken = $service->session->getAccessToken();
            $service->refreshToken = $service->session->getRefreshToken();

            $session->set('accessToken', $service->session->getAccessToken());
            $session->set('refreshToken', $service->session->getRefreshToken());

            return $this->redirect('/');
        } else {
            $options = $service->getAuthOptions(null);
            return $this->redirect($service->session->getAuthorizeUrl($options));
        }
        return 0;
    }

    // https://clarknelson.com.ddev.site/actions/craft-spotify/spotify/testing
    public function actionTesting()
    {
        $me = CraftSpotify::getInstance()->spotify->getMe();
        dd($me);
        return 0;
    }
}
