<?php

namespace VMware\Tests;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Psr7\Request;
use PHPUnit\Framework\TestCase;
use VMware\Middleware\AuthMiddleware;

class AuthMiddlewareTest extends TestCase
{
    public function testInvokeAddsSessionHeader(): void
    {
        // Mock authentication response
        $authMock = new MockHandler([
            new Response(200, [], json_encode(['value' => 'fake-session-id'])),
        ]);
        $authClient = new Client(['handler' => HandlerStack::create($authMock), 'vmware_user' => 'u', 'vmware_password' => 'p']);

        $middleware = new AuthMiddleware($authClient);

        // Mock actual request
        $mock = new MockHandler([
            new Response(200, [], 'ok'),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push($middleware);
        $client = new Client(['handler' => $stack]);

        $client->request('GET', '/');

        // Check if session header was added to the request
        $request = $mock->getLastRequest();
        $this->assertTrue($request->hasHeader('vmware-api-session-id'));
        $this->assertEquals('fake-session-id', $request->getHeaderLine('vmware-api-session-id'));
    }

    public function testInvokeGeneratesSessionOnlyOnce(): void
    {
        // Mock authentication response (should be called only once)
        $authMock = new MockHandler([
            new Response(200, [], json_encode(['value' => 'fake-session-id'])),
        ]);
        $authClient = new Client(['handler' => HandlerStack::create($authMock), 'vmware_user' => 'u', 'vmware_password' => 'p']);

        $middleware = new AuthMiddleware($authClient);

        // Mock actual requests
        $mock = new MockHandler([
            new Response(200, [], 'ok'),
            new Response(200, [], 'ok'),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push($middleware);
        $client = new Client(['handler' => $stack]);

        $client->request('GET', '/');
        $client->request('GET', '/');

        $this->assertEquals(0, $authMock->count()); // All auth requests consumed? No, count returns remaining.
        // Wait, MockHandler counts remaining items. We put 1 item.
        // After 1 call, it should be 0.
        // If it was called twice, it would throw because empty, unless we add more.

        $this->assertEquals(0, $authMock->count());
    }

    public function testAuthFailureThrowsException(): void
    {
        $authMock = new MockHandler([
            new Response(200, [], json_encode(['value' => ''])), // Empty session ID
        ]);
        $authClient = new Client(['handler' => HandlerStack::create($authMock), 'vmware_user' => 'u', 'vmware_password' => 'p']);

        $middleware = new AuthMiddleware($authClient);

        $mock = new MockHandler([
            new Response(200, [], 'ok'),
        ]);
        $stack = HandlerStack::create($mock);
        $stack->push($middleware);
        $client = new Client(['handler' => $stack]);

        $this->expectException(TransferException::class);
        $this->expectExceptionMessage('SessionId returned by the api is missing');

        $client->request('GET', '/');
    }
}
