<?php

namespace VMware;

use VMware\Middleware\AuthMiddleware;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\InvalidArgumentException;
use GuzzleHttp\HandlerStack;

class VMwareClient extends Client
{
    public function __construct(array $config = [])
    {
        if (empty($config['vmware_user'])) {
            throw new InvalidArgumentException('You must provide an vmware_user key');
        }

        if (empty($config['vmware_password'])) {
            throw new InvalidArgumentException('You must provide an vmware_password key');
        }

        $config = array_merge([
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ]
        ], $config);

        if (empty($config['handler'])) {
            $config['handler'] = HandlerStack::create();
        }
        
        $config['handler']->push(new AuthMiddleware(new Client([
            'base_uri' => $config['base_uri'],
            'vmware_user' => $config['vmware_user'],
            'vmware_password' => $config['vmware_password'],
        ])));

        parent::__construct($config);
    }
}
