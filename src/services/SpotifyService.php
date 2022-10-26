<?php
namespace clarknelson\spotify\services;

use clarknelson\spotify\Plugin as CraftSpotify;
use yii\base\Component;
use craft\helpers\App;
use craft\helpers\UrlHelper;

class SpotifyService extends Component
{
    // public $client = null;
    // public $defaultListId = null;

    public $authUrl = null;

    public $accessToken = null;
    public $refreshToken = null;

    public $session = null;

    private $api = null;

    private function buildAPI(){
        $session = \Craft::$app->getSession();

        // Use previously requested tokens fetched from somewhere. A database for example.
        $accessToken = $session->get('accessToken');
        $refreshToken = $session->get('refreshToken');
        if ($accessToken) {
            $this->session->setAccessToken($accessToken);
            $this->session->setRefreshToken($refreshToken);
        } else {
            // Or request a new access token
            $session->refreshAccessToken($refreshToken);
        }

        if(!$this->api){
            $this->api = new \SpotifyWebAPI\SpotifyWebAPI([
                'auto_refresh' => true,
            ], $this->session);

            // @TODO: i'm not totally sure what to do here
            // these should like be checked / saved against existing tokens
            $newAccessToken = $this->session->getAccessToken();
            $newRefreshToken = $this->session->getRefreshToken();
        }

        // $api->setAccessToken($session->get('accessToken'));
    }

    public function getMyTop($type = 'artists', $options = [ 'limit' => 20, 'offset' => 0, 'time_range' => 'medium_term' ]){
        $this->buildAPI();
   
        if($type != 'tracks' || $type != 'artists'){
            $type = 'artists';
        }

        try{
            return $this->api->getMyTop($type, $options)->items;
        } catch(\SpotifyWebAPI\SpotifyWebAPIException $e){
            dump($e);
            return [];
        }
    }

    public function getTrack($id){
        $this->buildAPI();

        try{
            return $this->api->getTrack($id);
        } catch(\SpotifyWebAPI\SpotifyWebAPIException $e){
            dump($e);
        }
    }

    public function getMe(){
        $this->buildAPI();

        try{
            return $this->api->me();
        } catch(\SpotifyWebAPI\SpotifyWebAPIException $e){
            dump($e);
        }
    }

    public function getMyCurrentTrack($options = []){
        $this->buildAPI();

        try{
            return $this->api->getMyCurrentTrack($options);
        } catch(\SpotifyWebAPI\SpotifyWebAPIException $e){
            dump($e);
            return null;
        }
    }

    public function getAuthOptions($state = null){
        return [
            // @TODO: probably do not need all of these
            // and they should be read in from a list in the settings.
            'scope' => [
                'user-read-currently-playing',
                'user-follow-read',
                'user-library-read', // https://developer.spotify.com/documentation/web-api/reference/#/operations/get-users-saved-tracks
                'user-top-read', // https://developer.spotify.com/documentation/web-api/reference/#/operations/get-users-top-artists-and-tracks
            ],
            'state' => $state,
        ];
    }

    public function init(): void{
        // first get the API key, the minimum for using the plugin.
        $clientId = CraftSpotify::getInstance()->settings->clientId;
        $clientId = App::env('SPOTIFY_CLIENT_ID') ?: $clientId;
        if(!$clientId){
            throw new \Exception('Spotify Client ID is not set.');
        }

        $clientSecret = CraftSpotify::getInstance()->settings->clientSecret;
        $clientSecret = App::env('SPOTIFY_CLIENT_SECRET') ?: $clientSecret;
        if(!$clientSecret){
            throw new \Exception('Spotify Client Secret Key is not set.');
        }
        
        if(!$this->session){
            $this->session = new \SpotifyWebAPI\Session(
                $clientId,
                $clientSecret,
                UrlHelper::siteUrl() . 'actions/craft-spotify/spotify/callback'
            );
        }
    }
}