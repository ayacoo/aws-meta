<?php

declare(strict_types=1);

namespace Ayacoo\AwsMeta\EventListener;

use Ayacoo\AwsMeta\Service\AwsImageRecognizeService;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Exception;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedEvent;
use TYPO3\CMS\Core\Resource\MetaDataAspect;
use TYPO3\CMS\Core\Utility\GeneralUtility;

class AfterFileAddedEventListener
{
    private array $extConf;

    public function __construct(
        private readonly AwsImageRecognizeService $awsImageRecognizeService,
        private readonly ExtensionConfiguration   $extensionConfiguration
    )
    {
        $this->extConf = $this->extensionConfiguration->get('aws_meta') ?? [];
    }

    /**
     * @throws Exception
     */
    public function setMetadata(AfterFileAddedEvent $event): AfterFileAddedEvent
    {
        if (!$this->hasAllAwsSettings()) {
            return $event;
        }

        $file = $event->getFile();
        $filePath = Environment::getPublicPath() . $file->getPublicUrl();

        $extension = strtolower($file->getExtension());
        $imageExtensions = ['jpg', 'png'];
        if (in_array($extension, $imageExtensions, true) && !empty($file->getPublicUrl())) {
            /** @var MetaDataAspect $metaData */
            $metaData = $file->getMetaData();
            $keywords = $this->awsImageRecognizeService->detectLabels($filePath);
            if (!empty($keywords)) {
                $metaData->offsetSet('aws_labels', $keywords);
            }
            $detectedText = $this->awsImageRecognizeService->detectText($filePath);
            if (!empty($detectedText)) {
                $metaData->offsetSet('aws_text', $detectedText);
            }
            $metaData->save();

            $this->addMessageToFlashMessageQueue(
                'The metadata was updated via AWS Rekognition API',
                FlashMessage::INFO
            );
        }

        return $event;
    }

    protected function addMessageToFlashMessageQueue(string $message, int $severity = FlashMessage::ERROR): void
    {
        if (Environment::isCli()) {
            return;
        }

        $flashMessage = GeneralUtility::makeInstance(
            FlashMessage::class,
            $message,
            'AWS Rekognition Status',
            $severity,
            true
        );

        $flashMessageService = GeneralUtility::makeInstance(FlashMessageService::class);
        $defaultFlashMessageQueue = $flashMessageService->getMessageQueueByIdentifier();
        $defaultFlashMessageQueue->enqueue($flashMessage);
    }

    private function hasAllAwsSettings(): bool
    {
        return !empty($this->extConf['awsProfile']) && !empty($this->extConf['awsRegion']) && !empty($this->extConf['awsVersion']);
    }
}
