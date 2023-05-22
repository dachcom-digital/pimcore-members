<?php

namespace MembersBundle\Controller;

use Pimcore\Model;
use Pimcore\Tool\Console;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Tool\Storage;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Matcher\UrlMatcher;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Routing\RouterInterface;

class RequestController extends AbstractController
{
    public const BUFFER_SIZE = 8192;

    public function __construct(
        protected RouterInterface $router,
        protected Storage $storage,
        protected Configuration $configuration,
        protected RestrictionUri $restrictionUri
    ) {
    }

    public function serveAction(Request $request, ?string $hash = null): StreamedResponse
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

    public function serveProtectedAssetPathAction(Request $request, ?string $hash = null): Response
    {
         if ($this->configuration->getConfig('restriction')['enabled'] === false) {
            throw $this->createNotFoundException('members restriction has been disabled.');
        }

        if (empty($hash)) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        $dataToProcess = $this->restrictionUri->decodeAssetUrl($hash);

        if ($dataToProcess === null || count($dataToProcess) === 0) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        return $this->servePath($request, $dataToProcess[0]);
    }

    private function servePath(Request $request, string $path): Response
    {
        if (!preg_match('@.*/(image|video)-thumb__[\d]+__.*@', $path, $matches)) {
            return throw $this->createNotFoundException();
        }

        $pimcoreThumbnailRoute = '_pimcore_service_thumbnail';
        $storage = $this->storage->get('thumbnail');
        $storagePath = urldecode($path);

        if ($storage->fileExists($storagePath)) {
            $stream = $storage->readStream($storagePath);

            return new StreamedResponse(function () use ($stream) {
                fpassthru($stream);
            }, 200, [
                'Content-Type' => $storage->mimeType($storagePath),
            ]);
        }

        $collection = new RouteCollection();
        $collection->add($pimcoreThumbnailRoute, $this->router->getRouteCollection()->get($pimcoreThumbnailRoute));
        $matcher = new UrlMatcher($collection, $this->router->getContext());

        return $this->forward('Pimcore\Bundle\CoreBundle\Controller\PublicServicesController::thumbnailAction', $matcher->matchRequest($request));
    }

    private function serveFile(Model\Asset $asset): StreamedResponse
    {
        $contentType = $asset->getMimetype();
        $fileSize = $asset->getFileSize();

        $response = new StreamedResponse();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Connection', 'Keep-Alive');
        $response->headers->set('Expires', 0);
        $response->headers->set('Provider', 'Pimcore-Members');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Length', $fileSize);
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            \Pimcore\File::getValidFilename(basename($asset->getFileName()))
        ));

        $response->setCallback(function () use ($asset) {
            flush();
            ob_flush();
            $handle = fopen(rawurldecode($asset->getLocalFile()), 'rb');
            while (!feof($handle)) {
                echo fread($handle, self::BUFFER_SIZE);
                flush();
                ob_flush();
            }
        });

        return $response;
    }

    /**
     * @throws \Exception
     */
    private function serveZip(array $assets): StreamedResponse
    {
        $fileName = 'package.zip';
        $files = '';

        /** @var Model\Asset $asset */
        foreach ($assets as $asset) {
            $filePath = rawurldecode($asset->getLocalFile());
            $files .= '"' . $filePath . '" ';
        }

        $response = new StreamedResponse();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'application/zip');
        $response->headers->set('Content-Transfer-Encoding', 'binary');
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            $fileName
        ));

        $zibLib = Console::getExecutable('zip');
        if (empty($zibLib)) {
            throw new NotFoundHttpException('zip extension not found on this server.');
        }

        $response->setCallback(function () use ($files) {
            mb_http_output('pass');
            flush();
            ob_flush();
            $handle = popen('zip -r -j - ' . $files, 'r');
            while (!feof($handle)) {
                echo fread($handle, self::BUFFER_SIZE);
                flush();
                ob_flush();
            }
            pclose($handle);
        });

        return $response;
    }
}
