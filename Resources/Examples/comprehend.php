<?php

use Aws\Comprehend\ComprehendClient;
use Aws\Exception\AwsException;

require '../../vendor/autoload.php';


$options = [
    'profile' => 'USERNAME',
    'region' => 'REGION',
    'version' => 'latest'
];
$client = new ComprehendClient($options);

$text = file_get_contents('/var/www/html/public/examples/comprehend.txt');

try {
    echo '<h3>DetectDominantLanguage</h3>';
    $result = $client->detectDominantLanguage([
        'Text' => $text
    ]);
    echo '<pre>';
    print_r($result);
    echo '</pre>';

    echo '<h3>Detect entities</h3>';
    $result = $client->detectEntities([
        'Text' => $text,
        'LanguageCode' => 'en'
    ]);
    echo '<pre>';
    print_r($result);
    echo '</pre>';

    echo '<h3>DetectKeyPhrases</h3>';
    $result = $client->detectKeyPhrases([
        'Text' => $text,
        'LanguageCode' => 'en'
    ]);

    $list = [];
    foreach ($result as $resultItem) {
        foreach ($resultItem as $item) {
            if (($item['Score'] ?? 0.00) > 0.9999) {
                $list[] = $item['Text'];
            }
        }
    }

    echo '<pre>';
    print_r($list);
    echo '</pre>';


} catch (AwsException $e) {
    echo '<pre>Error: $e</pre>';
}
