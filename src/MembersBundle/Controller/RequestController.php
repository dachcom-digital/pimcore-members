<?php

namespace MembersBundle\Controller;

use Pimcore\Model;
use Pimcore\Tool\Console;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\RestrictionUri;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestController extends AbstractController
{
    public const BUFFER_SIZE = 8192;

    protected Configuration $configuration;
    protected RestrictionUri $restrictionUri;

    public function __construct(Configuration $configuration, RestrictionUri $restrictionUri)
    {
        $this->configuration = $configuration;
        $this->restrictionUri = $restrictionUri;
    }

    public function serveAction(?string $hash = null): StreamedResponse
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

    private function serveFile(Model\Asset $asset): StreamedResponse
    {
        $response = new StreamedResponse(static fn () => fpassthru($asset->getStream()), Response::HTTP_OK);
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
