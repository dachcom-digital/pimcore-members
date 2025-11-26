<?php

/*
 * This source file is available under two different licenses:
 *   - GNU General Public License version 3 (GPLv3)
 *   - DACHCOM Commercial License (DCL)
 * Full copyright and license information is available in
 * LICENSE.md which is distributed with this source code.
 *
 * @copyright  Copyright (c) DACHCOM.DIGITAL AG (https://www.dachcom-digital.com)
 * @license    GPLv3 and DCL
 */

namespace MembersBundle\Controller;

use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Model;
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
            return new BinaryFileResponse(PIMCORE_WEB_ROOT . '/bundles/pimcoreadmin/img/filetype-not-supported.svg');
        }

        return $this->servePath($decodedPath, $id, $request);
    }

    private function servePath(string $path, int $id, Request $request): Response
    {
        $asset = Model\Asset::getById($id);

        if ($asset instanceof Model\Asset\Video) {
            try {
                return $this->serveVideoAsset($asset, $path, $request);
            } catch (\Throwable $e) {
                return new BinaryFileResponse(PIMCORE_WEB_ROOT . '/bundles/pimcoreadmin/img/filetype-not-supported.svg');
            }
        }

        $response = Model\Asset\Service::getStreamedResponseByUri($path);

        if ($response instanceof StreamedResponse) {
            return $response;
        }

        // no thumbnail path found, check if the original file has been requested
        if ($asset instanceof Model\Asset) {

            $stream = $asset->getStream();

            return new StreamedResponse(function () use ($stream) {
                fpassthru($stream);
            }, Response::HTTP_OK, [
                'Content-Type'   => $asset->getMimeType(),
                'Content-Length' => $asset->getFileSize(),
            ]);
        }

        return throw $this->createNotFoundException();
    }

    private function serveVideoAsset(Model\Asset\Video $asset, string $path, Request $request): Response
    {
        $regExpression = sprintf('/(%s)(%s)-thumb__(%s)__(%s)\/(%s)/',
            '.*',
            'video|image',
            '\d+',
            '[a-zA-Z0-9_\-]+',
            '.*'
        );

        if (preg_match($regExpression, $path, $matches)) {
            $storage = $this->storage->get('thumbnail');
        } else {
            $storage = $this->storage->get('asset');
        }

        if (!$storage->fileExists($path)) {
            throw new \InvalidArgumentException('File does not exist');
        }

        $fileSize = $storage->fileSize($path);

        $start = 0;
        $end = $fileSize - 1;
        $statusCode = Response::HTTP_OK;

        if ($range = $request->headers->get('Range')) {
            if (preg_match('/bytes=(\d*)-(\d*)/', $range, $matches)) {
                if ($matches[1] !== '') {
                    $start = (int) $matches[1];
                }

                if ($matches[2] !== '') {
                    $end = (int) $matches[2];
                }

                if ($end > $fileSize - 1) {
                    $end = $fileSize - 1;
                }

                $statusCode = Response::HTTP_PARTIAL_CONTENT;
            }
        }

        $length = $end - $start + 1;

        $response = new StreamedResponse(function () use ($storage, $path, $start, $length) {

            $handle = $storage->readStream($path);

            fseek($handle, $start);
            $bytesLeft = $length;
            $chunkSize = 8192;

            while ($bytesLeft > 0 && !feof($handle)) {
                $readSize = min($chunkSize, $bytesLeft);
                echo fread($handle, $readSize);
                flush();
                $bytesLeft -= $readSize;
            }

            fclose($handle);
        }, $statusCode);

        $response->headers->set('Content-Type', $storage->mimeType($path));
        $response->headers->set('Accept-Ranges', 'bytes');
        $response->headers->set('Content-Length', $length);

        if ($statusCode === Response::HTTP_PARTIAL_CONTENT) {
            $response->headers->set('Content-Range', "bytes {$start}-{$end}/{$fileSize}");
        }

        return $response;
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
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            \Pimcore\File::getValidFilename(basename($asset->getFileName()))
        ));

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
