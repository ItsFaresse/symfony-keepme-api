<?php
namespace App\Service;

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;

class EmailService
{
    public function sendEmail($data)
    {
        $client = new Client();

        // Evolution : sortir le content-type de cette méthode
        // Pour éviter que l'envoi de mail soit verrouillé avec application/json
        $headers = ['content-type' => "application/json"];
        $request = new Request('POST', 'http://169.51.4.250/email', $headers, json_encode($data));

        $response = $client->send($request);

        return $response->getBody()->getContents();
    }
}