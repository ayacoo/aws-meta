<?php

require '../../vendor/autoload.php';

use Aws\Rekognition\RekognitionClient;

$options = [
    'profile' => 'USERNAME',
    'region' => 'REGION',
    'version' => 'latest'
];
$rekognition = new RekognitionClient($options);

$photo = '/var/www/html/public/examples/image.jpg';
$fp_image = fopen($photo, 'r');
$image = fread($fp_image, filesize($photo));
fclose($fp_image);

$result = $rekognition->detectText([
        'Image' => [
            'Bytes' => $image,
        ],
        'Attributes' => ['ALL']
    ]
);

echo '<pre>';
print_r($result);
echo '</pre>';



$keywords = [];
foreach ($result ?? [] as $labels) {
    if (is_array($labels)) {
        foreach ($labels ?? [] as $label) {
            $percent = (float)($label['Confidence'] ?? 0.00);
            $name = $label['DetectedText'] ?? '';
            if ($percent > 90 && !empty($name)) {
                $keywords[] = $name;
            }
        }
    }
}

echo implode(', ', array_unique($keywords));
