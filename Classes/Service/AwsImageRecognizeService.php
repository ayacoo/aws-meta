<?php

declare(strict_types=1);

namespace Ayacoo\AwsMeta\Service;

use Aws\Exception\AwsException;
use Aws\Rekognition\RekognitionClient;
use Exception;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AwsImageRecognizeService
{
    private ?RekognitionClient $rekognitionClient;

    private array $extConf;
    private LoggerInterface $logger;

    public function __construct(ExtensionConfiguration $extensionConfiguration)
    {
        $this->extConf = $extensionConfiguration->get('aws_meta') ?? [];
        $options = [
            'profile' => $this->extConf['awsProfile'],
            'region' => $this->extConf['awsRegion'],
            'version' => $this->extConf['awsVersion']
        ];
        $this->rekognitionClient = new RekognitionClient($options);
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    /**
     * @param string $imagePath
     * @return string
     */
    public function detectLabels(string $imagePath): string
    {
        $result = $this->recognizeImage($imagePath, 'detectLabels');

        $keywords = [];
        foreach ($result as $labels) {
            if (is_array($labels)) {
                foreach ($labels ?? [] as $label) {
                    $confidence = (float)($label['Confidence'] ?? 0.00);
                    $name = $label['Name'] ?? '';
                    if (($confidence > $this->extConf['confidence']) && !empty($name)) {
                        $keywords[] = $name;
                    }
                }
            }
        }

        return implode(', ', $keywords);
    }

    /**
     * @param string $imagePath
     * @return string
     */
    public function detectText(string $imagePath): string
    {
        $result = $this->recognizeImage($imagePath, 'detectText');

        $detectedTextItems = [];
        foreach ($result as $labels) {
            if (is_array($labels)) {
                foreach ($labels ?? [] as $label) {
                    $confidence = (float)($label['Confidence'] ?? 0.00);
                    $detectedText = $label['DetectedText'] ?? '';
                    if (($confidence > $this->extConf['confidence']) && !empty($detectedText)) {
                        $detectedTextItems[] = $detectedText;
                    }
                }
            }
        }

        return implode(', ', array_unique($detectedTextItems));
    }

    /**
     * @param string $imagePath
     * @param string $function
     */
    protected function recognizeImage(string $imagePath, string $function)
    {
        $fpImage = fopen($imagePath, 'rb');
        $image = fread($fpImage, filesize($imagePath));
        fclose($fpImage);

        try {
            return $this->rekognitionClient->$function([
                    'Image' => [
                        'Bytes' => $image,
                    ],
                    'Attributes' => ['ALL']
                ]
            );
        } catch (AwsException $e) {
            $this->logger->error('AWS Exception', [$e->getAwsRequestId(), $e->getAwsErrorType(), $e->getAwsErrorCode()]);
            return [];
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
    }
}
