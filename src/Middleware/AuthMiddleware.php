<?php

namespace VMware\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Psr7\Request;

class AuthMiddleware
{
    private $sessionId;

    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }
    
    public function __invoke(callable $handler)
    {
        if (empty($this->sessionId)) {
            $this->generateSessionId();
        }

        return function (Request $request, array $options) use ($handler) {
            return $handler(
                $request->withHeader('vmware-api-session-id', $this->sessionId),
                $options
            );
        };
    }

    private function generateSessionId()
    {
        $response = $this->client->post('/rest/com/vmware/cis/session', [
            'headers' => [
                'Authorization' => sprintf(
                    'Basic %s', 
                    base64_encode(
                        sprintf('%s:%s', $this->client->getConfig('vmware_user'), $this->client->getConfig('vmware_password'))
                    )
                ),
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ]);

        $json = json_decode($response->getBody()->getContents(), \JSON_THROW_ON_ERROR);
        if (empty($json['value'])) {
            throw new GuzzleException('SessionId returned by the api is missing');
        }

        $this->sessionId = $json['value'];
    }
}