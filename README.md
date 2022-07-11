VMware API
==========

[![Latest Stable Version](https://poser.pugx.org/mtxserv/vsphere-api/v/stable.png)](https://packagist.org/packages/mtxserv/vmware-api)

vSphere Api is a modern PHP library based on Guzzle for [VMware Rest API](https://developer.vmware.com/apis/vsphere-automation/latest/vcenter/).

## Dependencies

* PHP 7 / 8
* [Guzzle](http://www.guzzlephp.org): ^7.0

## Installation

Installation of VMware Rest Api is only officially supported using Composer:

```sh
composer require mtxserv/vmware-api
```

## Example

```php
<?php

use VMware\VMwareClient;
use GuzzleHttp\Exception\GuzzleException;

$client = new VMwareClient([
    'base_uri' => 'https://my-pcc.ovh.com',
    'vmware_user' => 'my_user',
    'vmware_password' => 'my_password',
]);

try {
    // Get VM list
    $response = $client->get('/rest/vcenter/vm');
    $json = json_decode($response->getBody()->getContents(), \JSON_THROW_ON_ERROR);
    var_dump($json);
} catch (GuzzleException $e) {
    var_dump($e->getMessage());
}
```
