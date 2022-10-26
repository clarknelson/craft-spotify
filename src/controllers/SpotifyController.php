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
        $session->set('spotifyState', $state);
        $options = $service->getAuthOptions($state);

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
            $options = $service->getAuthOptions();
            return $this->redirect($service->session->getAuthorizeUrl($options));
        }
        return 0;
    }

    // https://clarknelson.com.ddev.site/actions/craft-spotify/spotify/testing
    public function actionTesting()
    {
        $session = Craft::$app->getSession();
        $api = new \SpotifyWebAPI\SpotifyWebAPI();
        

        // Fetch the saved access token from somewhere. A session for example.
        $api->setAccessToken($session->get('accessToken'));

        try{
            // It's now possible to request data about the currently authenticated user
            dump( $api->me() );

            // Getting Spotify catalog data is of course also possible
            dump( $api->getTrack('7EjyzZcbLxW7PaaLua9Ksb') );
        } catch(\SpotifyWebAPI\SpotifyWebAPIException $e){
            if($e->getMessage() == "The access token expired"){
                return $this->redirect('/actions/craft-spotify/spotify/auth');
            }
            dump($e);
        }
     
        return 0;
    }


    public function actionSubscribe($id)
    {
        dump($id);
    }
}
