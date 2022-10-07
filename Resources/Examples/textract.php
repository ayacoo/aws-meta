<link rel='preconnect' href='https://fonts.googleapis.com'>
<link rel='preconnect' href='https://fonts.gstatic.com' crossorigin>
<link href='https://fonts.googleapis.com/css2?family=Open+Sans:wght@300&display=swap' rel='stylesheet'>

<style>
    * {
        font-family: 'Open Sans', sans-serif;
    }
</style>

<?php

require '../../vendor/autoload.php';

use Aws\Exception\AwsException;
use Aws\Textract\TextractClient;

// Definiere komplette Bildabmessungen

$xBasic = 3226;
$yBasic = 1814;

// Definiere Bildausschnitt
$xMin = 1280;
$xMax = 1495;

$yMin = 945;
$yMax = 1020;

$options = [
    'profile' => 'USERNAME',
    'region' => 'REGION',
    'version' => 'latest'
];
$client = new TextractClient($options);

$photo = '/var/www/html/public/examples/textract.jpg';
$fp_image = fopen($photo, 'rb');
$image = fread($fp_image, filesize($photo));
fclose($fp_image);

try {
    $result = $client->detectDocumentText([
        'Document' => [
            'Bytes' => $image,
        ],
    ]);

    echo '<pre>';
    print_r($result);
    echo '</pre>';

    echo '<h1>AWS Textract API</h1>';
    echo '<img src="/examples/textract.jpg" width="1080"><br />';
    $i = 0;
    echo '<table border=0 cellspacing="5" cellpadding="5">
        <tr>
            <th>#</th>
            <th>BlockType</th>
            <th>Text</th>
            <th>Confidence</th>
        </tr>';
    foreach ($result['Blocks'] as $phrase) {
        if ($phrase['BlockType'] === 'LINE') {

            // Polygon Check
            $polygon = $phrase['Geometry']['Polygon'];
            $textIsInArea = true;
            foreach ($polygon as $point) {
                $xValue = round($point['X'] * $xBasic);
                $yValue = round($point['Y'] * $yBasic);

                // echo $xValue . ' - ' . $yValue . '<br/>';

                $xCheck = ($xMin <= $xValue) && ($xValue <= $xMax);
                $yCheck = ($yMin <= $yValue) && ($yValue <= $yMax);

                if (!$xCheck || !$yCheck) {
                    $textIsInArea = false;
                    break;
                }
            }

            //if ($textIsInArea) {
                $i++;
                echo '<tr><td>' . $i . '</td>';
                echo '<td>' . $phrase['BlockType'] . '</td>';
                echo '<td>' . $phrase['Text'] . '</td>';
                echo '<td>' . round($phrase['Confidence']) . '%</td>';
                echo '</tr>';
            //}
        }
    }
    echo '</table>';

} catch (AwsException $e) {
    echo '<pre>Error: $e</pre>';
}
