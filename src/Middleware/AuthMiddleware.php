<?php

namespace VMware\Middleware;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use Psr\Http\Message\RequestInterface;

class AuthMiddleware
{
    private ?string $sessionId = null;

    public function __construct(
        private readonly Client $client
    ) {
    }

    public function __invoke(callable $handler): callable
    {
        return function (RequestInterface $request, array $options) use ($handler) {
            if (empty($this->sessionId)) {
                $this->generateSessionId();
            }

            return $handler(
                $request->withHeader('vmware-api-session-id', $this->sessionId),
                $options
            );
        };
    }

    private function generateSessionId(): void
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

        $json = json_decode($response->getBody()->getContents(), true, 512, \JSON_THROW_ON_ERROR);
        if (empty($json['value'])) {
            throw new TransferException('SessionId returned by the api is missing');
        }

        $this->sessionId = $json['value'];
    }
}
