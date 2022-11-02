<?php

namespace clarknelson\spotify\models;

use craft\base\Model;

class Settings extends Model
{
    public $clientId = null;
    public $clientSecret = null;
    public $accessToken = null;
    public $refreshToken = null;

    public function rules(): array
    {
        return [
            [['clientId', 'clientSecret'], 'required'],
        ];
    }
}