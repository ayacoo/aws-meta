<?php

require '../../vendor/autoload.php';

use Aws\Rekognition\RekognitionClient;

$options = [
    'profile' => 'USERNAME',
    'region' => 'REGION',
    'version' => 'latest',
];
$rekognition = new RekognitionClient($options);

$photo = '/var/www/html/public/examples/detectLabels.jpg';
$fp_image = fopen($photo, 'rb');
$image = fread($fp_image, filesize($photo));
fclose($fp_image);

$confidence = 90;

$result = $rekognition->detectLabels(
    [
        'LanguageCode' => 'de-DE',
        'Image' => [
            'Bytes' => $image,
        ],
        'Attributes' => ['ALL'],
    ]
);

echo '<pre>';
print_r($result);
echo '</pre>';

$detectedTextItems = [];
foreach ($result ?? [] as $labels) {
    if (is_array($labels)) {
        foreach ($labels ?? [] as $label) {
            $percent = (float)($label['Confidence'] ?? 0.00);
            $name = $label['Name'] ?? '';
            if ($percent > $confidence && !empty($name)) {
                $detectedTextItems[] = $name;
            }
        }
    }
}

echo implode(', ', array_unique($detectedTextItems));
