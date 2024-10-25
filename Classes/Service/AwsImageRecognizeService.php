<?php

declare(strict_types=1);

namespace Ayacoo\AwsMeta\Service;

use Aws\Exception\AwsException;
use Aws\Rekognition\RekognitionClient;
use Aws\ResultInterface;
use Exception;
use Psr\Log\LoggerInterface;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationExtensionNotConfiguredException;
use TYPO3\CMS\Core\Configuration\Exception\ExtensionConfigurationPathDoesNotExistException;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AwsImageRecognizeService
{
    private array $extConf;

    /**
     * @throws ExtensionConfigurationPathDoesNotExistException
     * @throws ExtensionConfigurationExtensionNotConfiguredException
     */
    public function __construct(
        protected ExtensionConfiguration $extensionConfiguration,
        private LoggerInterface $logger,
        private ?RekognitionClient $rekognitionClient
    ) {
        $this->extConf = $this->extensionConfiguration->get('aws_meta') ?? [];
        $options = [
            'profile' => $this->extConf['awsProfile'],
            'region' => $this->extConf['awsRegion'],
            'version' => $this->extConf['awsVersion'],
        ];
        $this->rekognitionClient = new RekognitionClient($options);
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }

    public function detectLabels(string $imagePath): string
    {
        $result = $this->recognizeImage($imagePath, 'detectLabels');

        $keywords = [];
        foreach ($result as $labels) {
            if (is_array($labels)) {
                foreach ($labels as $label) {
                    $confidence = (float)($label['Confidence'] ?? 0.00);
                    $name = $label['Name'] ?? '';
                    if (($confidence > $this->extConf['confidence']) && $name !== '') {
                        $keywords[] = $name;
                    }
                }
            }
        }

        return implode(', ', array_unique($keywords));
    }

    public function detectText(string $imagePath): string
    {
        $result = $this->recognizeImage($imagePath, 'detectText');

        $detectedTextItems = [];
        foreach ($result as $labels) {
            if (is_array($labels)) {
                foreach ($labels as $label) {
                    $confidence = (float)($label['Confidence'] ?? 0.00);
                    $detectedText = $label['DetectedText'] ?? '';
                    if (($confidence > $this->extConf['confidence']) && $detectedText !== '') {
                        $detectedTextItems[] = $detectedText;
                    }
                }
            }
        }

        return implode(', ', array_unique($detectedTextItems));
    }

    protected function recognizeImage(string $imagePath, string $function): array|ResultInterface
    {
        $fpImage = fopen($imagePath, 'rb');
        $image = fread($fpImage, filesize($imagePath));
        fclose($fpImage);

        try {
            $arguments = [
                'Image' => [
                    'Bytes' => $image,
                ],
                'Attributes' => ['ALL'],
            ];

            return match ($function) {
                'detectLabels' => $this->rekognitionClient->detectLabels($arguments),
                'detectText' => $this->rekognitionClient->detectText($arguments),
                default => [],
            };
        } catch (AwsException $e) {
            $this->logger->error(
                'AWS Exception',
                [$e->getAwsRequestId(), $e->getAwsErrorType(), $e->getAwsErrorCode()]
            );
            return [];
        } catch (Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
    }
}
