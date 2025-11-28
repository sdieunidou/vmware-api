<?php

namespace VMware\Tests;

use GuzzleHttp\Exception\InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use VMware\VMwareClient;

class VMwareClientTest extends TestCase
{
    public function testConstructThrowsExceptionWithoutUser(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide an vmware_user key');

        new VMwareClient(['vmware_password' => 'password']);
    }

    public function testConstructThrowsExceptionWithoutPassword(): void
    {
        $this->expectException(InvalidArgumentException::class);
        $this->expectExceptionMessage('You must provide an vmware_password key');

        new VMwareClient(['vmware_user' => 'user']);
    }

    public function testConstructSuccess(): void
    {
        $client = new VMwareClient([
            'vmware_user' => 'user',
            'vmware_password' => 'password',
            'base_uri' => 'https://example.com'
        ]);

        $this->assertInstanceOf(VMwareClient::class, $client);
        $this->assertEquals('application/json', $client->getConfig('headers')['Content-Type']);
    }
}
