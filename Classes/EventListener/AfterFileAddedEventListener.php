<?php

declare(strict_types=1);

namespace Ayacoo\AwsMeta\EventListener;

use Ayacoo\AwsMeta\Service\AwsImageRecognizeService;
use TYPO3\CMS\Core\Attribute\AsEventListener;
use TYPO3\CMS\Core\Configuration\ExtensionConfiguration;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Messaging\FlashMessage;
use TYPO3\CMS\Core\Messaging\FlashMessageService;
use TYPO3\CMS\Core\Resource\Event\AfterFileAddedEvent;
use TYPO3\CMS\Core\Resource\File;
use TYPO3\CMS\Core\Type\ContextualFeedbackSeverity;
use TYPO3\CMS\Core\Utility\GeneralUtility;

#[AsEventListener(
    identifier: 'ayacoo/aws-meta/after-file-added-event-listener'
)]
class AfterFileAddedEventListener
{
    private array $extConf;

    public function __construct(
        private readonly AwsImageRecognizeService $awsImageRecognizeService,
        private readonly ExtensionConfiguration $extensionConfiguration
    ) {
        $this->extConf = $this->extensionConfiguration->get('aws_meta') ?? [];
    }

    public function __invoke(AfterFileAddedEvent $event): void
    {
        if (!$this->hasAllAwsSettings()) {
            return;
        }

        /** @var File $file */
        $file = $event->getFile();
        $filePath = Environment::getPublicPath() . $file->getPublicUrl();

        $extension = strtolower($file->getExtension());
        $imageExtensions = ['jpg', 'png'];
        if (in_array($extension, $imageExtensions, true) && $file->getPublicUrl() !== null) {
            $metaData = $file->getMetaData();
            $keywords = $this->awsImageRecognizeService->detectLabels($filePath);
            if ($keywords !== '') {
                $metaData->offsetSet('aws_labels', $keywords);
            }
            $detectedText = $this->awsImageRecognizeService->detectText($filePath);
            if ($detectedText !== '') {
                $metaData->offsetSet('aws_text', $detectedText);
            }
            $metaData->save();

            $this->addMessageToFlashMessageQueue(
                'The metadata was updated via AWS Rekognition API',
                ContextualFeedbackSeverity::INFO
            );
        }
    }

    protected function addMessageToFlashMessageQueue(
        string $message,
        ContextualFeedbackSeverity $severity = ContextualFeedbackSeverity::ERROR
    ): void {
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
        return $this->extConf['awsProfile'] !== '' && $this->extConf['awsRegion'] !== '' &&
            $this->extConf['awsVersion'] !== '';
    }
}
