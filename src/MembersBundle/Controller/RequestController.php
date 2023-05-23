<?php

namespace MembersBundle\Controller;

use Pimcore\Model;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Tool\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\File\Stream;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\RouterInterface;

class RequestController extends AbstractController
{
    public function __construct(
        protected RouterInterface $router,
        protected Storage $storage,
        protected Configuration $configuration,
        protected RestrictionUri $restrictionUri
    ) {
    }

    public function serveAction(Request $request, ?string $hash = null): Response
    {
        if ($this->configuration->getConfig('restriction')['enabled'] === false) {
            throw $this->createNotFoundException('members restriction has been disabled.');
        }

        if (empty($hash)) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        $dataToProcess = $this->restrictionUri->decodeAssetUrl($hash);

        if ($dataToProcess === null) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        if (count($dataToProcess) === 1) {
            return $this->serveFile($dataToProcess[0]);
        }

        if (count($dataToProcess) > 1) {
            return $this->serveZip($dataToProcess);
        }

        throw $this->createNotFoundException('invalid hash for asset request.');
    }

    public function serveProtectedAssetPathAction(Request $request, int $id, string $path, string $extension): Response
    {
        if ($this->configuration->getConfig('restriction')['enabled'] === false) {
            throw $this->createNotFoundException('members restriction has been disabled.');
        }

        $decodedPath = $this->restrictionUri->decodePublicAssetUrl($id, $path, $extension);

        if ($decodedPath === null) {
            return new BinaryFileResponse(PIMCORE_PATH . '/bundles/AdminBundle/Resources/public/img/filetype-not-supported.svg');
        }

        return $this->servePath($decodedPath);
    }

    private function servePath(string $path): Response
    {
        // @todo: replace this with Asset\Service::getStreamedResponseByUri() in P11

        $regExpression = sprintf('/(%s)(%s)-thumb__(%s)__(%s)\/(%s)/',
            '.*',
            'video|image',
            '\d+',
            '[a-zA-Z0-9_\-]+',
            '.*'
        );

        $storage = $this->storage->get('thumbnail');
        $storagePath = urldecode($path);

        if ($storage->fileExists($storagePath)) {
            $stream = $storage->readStream($storagePath);

            return new StreamedResponse(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                'Content-Type'   => $storage->mimeType($storagePath),
                'Content-Length' => $storage->fileSize($storagePath),
            ]);
        }

        if (preg_match($regExpression, $path, $matches)) {
            $data = [
                'prefix'        => ltrim($matches[1], '/'),
                'type'          => $matches[2],
                'assetId'       => $matches[3],
                'thumbnailName' => $matches[4],
                'filename'      => $matches[5],
            ];
        } else {
            return throw $this->createNotFoundException();
        }

        return $this->forward('Pimcore\Bundle\CoreBundle\Controller\PublicServicesController::thumbnailAction', $data);
    }

    private function serveFile(Model\Asset $asset): StreamedResponse
    {
        $response = new StreamedResponse(static function () use ($asset) {
            fpassthru($asset->getStream());
        });

        $response->headers->set('Content-Type', $asset->getMimetype());
        $response->headers->set('Connection', 'Keep-Alive');
        $response->headers->set('Expires', 0);
        $response->headers->set('Provider', 'Pimcore-Members');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Length', $asset->getFileSize());
        $response->headers->set('Content-Disposition',
            $response->headers->makeDisposition(
                ResponseHeaderBag::DISPOSITION_ATTACHMENT,
                \Pimcore\File::getValidFilename(basename($asset->getFileName()))
            )
        );

        return $response;
    }

    private function serveZip(array $assets): BinaryFileResponse
    {
        $fileName = 'package';
        $tempZipPath = sprintf('%s/%s-%s.zip', PIMCORE_SYSTEM_TEMP_DIRECTORY, uniqid('', false), $fileName);

        $archive = new \ZipArchive();
        $archive->open($tempZipPath, \ZipArchive::CREATE);

        /** @var Model\Asset $asset */
        foreach ($assets as $asset) {
            $archive->addFromString($asset->getFilename(), stream_get_contents($asset->getStream()));
        }

        $archive->close();

        $response = new BinaryFileResponse(new Stream($tempZipPath));
        $response->deleteFileAfterSend(true);

        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            sprintf('%s.zip', $fileName)
        ));

        return $response;
    }
}
