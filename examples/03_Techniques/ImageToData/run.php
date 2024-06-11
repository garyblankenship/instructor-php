# Image to data

This is an example of how to extract structured data from an image using
Instructor. The image is loaded from a file and converted to base64 format
before sending it to OpenAI API.

The response model is a PHP class that represents the structured receipt
information with data of vendor, items, subtotal, tax, tip, and total.

```php
<?php
$loader = require 'vendor/autoload.php';
$loader->add('Cognesy\\Instructor\\', __DIR__ . '../../src/');

use Cognesy\Instructor\Extras\Image\Image;
use Cognesy\Instructor\Instructor;

class Vendor {
    public ?string $name = '';
    public ?string $address = '';
    public ?string $phone = '';
}

class ReceiptItem {
    public string $name;
    public ?int $quantity = 1;
    public float $price;
}

class Receipt {
    public Vendor $vendor;
    /** @var ReceiptItem[] */
    public array $items = [];
    public ?float $subtotal;
    public ?float $tax;
    public ?float $tip;
    public float $total;
}

$imagePath = __DIR__ . '/receipt.png';

$receipt = (new Instructor)->respond(
    messages: Image::fromFile($imagePath)->toMessages(),
    responseModel: Receipt::class,
    prompt: 'Extract structured data from the receipt.',
    model: 'gpt-4-vision-preview',
    options: ['max_tokens' => 4096]
);


dump($receipt);
?>
```
