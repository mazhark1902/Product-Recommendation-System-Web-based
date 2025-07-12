<?php

namespace App\Services;

use Google_Client;
use Google_Service_Gmail;
use Google_Service_Gmail_Message;
use Illuminate\Support\Facades\Storage;

class GmailClient
{
    public static function getClient(): Google_Client
    {
        $client = new Google_Client();
        $client->setApplicationName('Gmail API Laravel');
        $client->setScopes(Google_Service_Gmail::GMAIL_READONLY);
        $client->setAuthConfig(storage_path('app/gmail/credentials.json'));
        $client->setRedirectUri('http://127.0.0.1:8000/');
        $client->setAccessType('offline');
        $client->setPrompt('select_account consent');

        $tokenPath = storage_path('app/gmail/token.json');
        if (file_exists($tokenPath)) {
            $accessToken = json_decode(file_get_contents($tokenPath), true);
            $client->setAccessToken($accessToken);
        }

if ($client->isAccessTokenExpired()) {
    if ($client->getRefreshToken()) {
        $client->fetchAccessTokenWithRefreshToken($client->getRefreshToken());
        file_put_contents($tokenPath, json_encode($client->getAccessToken()));
    } else {
        // Buat URL auth dan buka
        $authUrl = $client->createAuthUrl();
        echo "Open this URL in your browser:\n$authUrl\n";

        // Jalankan server lokal: php -S localhost:80
        echo "Paste the 'code' from redirected URL after login:\n";
        echo "Example: http://localhost/?code=xxxxxxxx -> paste xxxxxxxx\n";

        echo "Enter code: ";
        $authCode = trim(fgets(STDIN));

        $accessToken = $client->fetchAccessTokenWithAuthCode($authCode);
        file_put_contents($tokenPath, json_encode($accessToken));
        $client->setAccessToken($accessToken);
    }
}


        return $client;
    }

    public static function getGmailService(): Google_Service_Gmail
    {
        $client = self::getClient();
        return new Google_Service_Gmail($client);
    }
}
