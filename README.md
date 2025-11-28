# VMware API Client

A simple PHP Guzzle client for interacting with the VMware REST API.

## Requirements

- PHP 8.3 or higher
- Guzzle 7.8 or higher

## Installation

Install the package via Composer:

```bash
composer require mtxserv/vmware-api
```

## Usage

```php
use VMware\VMwareClient;

$client = new VMwareClient([
    'base_uri' => 'https://vcenter.example.com',
    'vmware_user' => 'administrator@vsphere.local',
    'vmware_password' => 'your_password',
    'verify' => false, // Set to true for production if you have valid certificates
]);

// Example: List VMs
try {
    $response = $client->get('/rest/vcenter/vm');
    $vms = json_decode($response->getBody(), true);
    print_r($vms);
} catch (\Exception $e) {
    echo 'Error: ' . $e->getMessage();
}
```

## Testing

Run the test suite:

```bash
vendor/bin/phpunit
```

## License

This project is licensed under the MIT License.
