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

$options = [
    'profile' => 'USERNAME',
    'region' => 'REGION',
    'version' => 'latest',
];
$awsTranscribeClient = new \Aws\TranscribeService\TranscribeServiceClient($options);

try {
    // see https://docs.aws.amazon.com/transcribe/latest/APIReference/API_StartTranscriptionJob.html
    $mediaUrl = 'S3_URL_MP4_VIDEO';
    $jobId = uniqid('AWS', true);

    $transcriptionResult = $awsTranscribeClient->startTranscriptionJob([
        'LanguageCode' => 'de-DE',
        'Media' => [
            'MediaFileUri' => $mediaUrl,
        ],
        'Settings' => [
            'VocabularyName' => 'YOUR_VOCABULARY_NAME',
        ],
        'TranscriptionJobName' => $jobId,
        'OutputBucketName' => 'BUCKETNAME',
        'OutputKey' => 'transcribe/',
        'Subtitles' => [
            'Formats' => [
                'vtt', 'srt',
            ],
            'OutputStartIndex' => 1,
        ],

    ]);

    $status = [];
    while (true) {
        $status = $awsTranscribeClient->getTranscriptionJob([
            'TranscriptionJobName' => $jobId,
        ]);

        if ($status->get('TranscriptionJob')['TranscriptionJobStatus'] === 'COMPLETED') {
            break;
        }

        sleep(5);
    }

    $url = $status->get('TranscriptionJob')['Transcript']['TranscriptFileUri'];
    $curl = curl_init();
    curl_setopt($curl, CURLOPT_URL, $url);
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_HEADER, false);
    $data = curl_exec($curl);
    if (curl_errno($curl)) {
        $error_msg = curl_error($curl);
        echo $error_msg;
    }
    curl_close($curl);
    $arr_data = json_decode($data, false);

    $file = '/var/www/html/public/examples/' . $jobId . '.txt';
    $txt = fopen($file, 'wb') or die('Unable to open file!');
    fwrite($txt, $arr_data->results->transcripts[0]->transcript);
    fclose($txt);

    echo 'Job done';
} catch (Exception $e) {
    echo $e->getMessage();
}
