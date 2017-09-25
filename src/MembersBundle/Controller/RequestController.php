<?php

namespace MembersBundle\Controller;

use Pimcore\Model;
use Pimcore\Tool\Console;
use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\RestrictionUri;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class RequestController extends AbstractController
{
    const BUFFER_SIZE = 8192;

    /**
     * @param null $hash
     *
     * @return StreamedResponse
     */
    public function serveAction($hash = NULL)
    {            
        if($this->container->get('members.configuration')->getConfig('restriction')['enabled'] === FALSE) {
            throw $this->createNotFoundException('members restriction has been disabled.');
        }

        if (empty($hash)) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        /** @var RestrictionUri $restrictionUri */
        $restrictionUri = $this->container->get('members.security.restriction.uri');
        $dataToProcess = $restrictionUri->decodeAssetUrl($hash);

        if ($dataToProcess === FALSE) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        if (count($dataToProcess) == 1) {
            return $this->serveFile($dataToProcess[0]);
        } else if (count($dataToProcess) > 1) {
            return  $this->serveZip($dataToProcess);
        } else {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }
    }

    /**
     * @param Model\Asset $asset
     *
     * @return StreamedResponse
     */
    private function serveFile(Model\Asset $asset)
    {
        $forceDownload = TRUE;
        $contentType = $asset->getMimetype();

        /** @var Configuration $configuration */
        $configuration = $this->container->get('members.configuration');
        $hasLuceneSearch = $configuration->hasBundle('LuceneSearchBundle\LuceneSearchBundle');

        if ($hasLuceneSearch === TRUE) {
            /** @var \LuceneSearchBundle\Tool\CrawlerState $crawlerState */
            $crawlerState = $this->container->get('lucene_search.tool.crawler_state');
            if ($crawlerState->isLuceneSearchCrawler() && in_array($asset->getMimetype(), ['application/pdf'])) {
                $forceDownload = FALSE;
            }
        }

        $response = new StreamedResponse();
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', $contentType);
        $response->headers->set('Connection', 'Keep-Alive');
        $response->headers->set('Expires', 0);
        $response->headers->set('Provider', 'Pimcore-Members');
        $response->headers->set('Cache-Control', 'must-revalidate, post-check=0, pre-check=0');
        $response->headers->set('Pragma', 'public');
        $response->headers->set('Content-Length', $asset->getFileSize('noformatting'));
        $response->headers->set('Content-Disposition', $response->headers->makeDisposition(
            $forceDownload ? ResponseHeaderBag::DISPOSITION_ATTACHMENT : ResponseHeaderBag::DISPOSITION_INLINE,
            basename($asset->getFileName())
        ));

        if ($forceDownload === FALSE) {
            $response->headers->set('Content-Description', 'File Transfer');
            $response->headers->set('Content-Transfer-Encoding', 'binary');
        }

        $response->setCallback(function () use($asset) {
            flush();
            ob_flush();
            $handle = fopen(rawurldecode(PIMCORE_ASSET_DIRECTORY . $asset->getFullPath()), 'rb');
            while (!feof($handle)) {
                print(fread($handle, self::BUFFER_SIZE));
                flush();
                ob_flush();
            }
        });

        return $response;

    }

    /**
     * @param $assets
     *
     * @return StreamedResponse
     */
    private function serveZip($assets)
    {
        $fileName = 'package.zip';
        $files = '';

        /** @var Model\Asset $asset */
        foreach ($assets as $asset) {
            $filePath = rawurldecode(PIMCORE_ASSET_DIRECTORY . $asset->getFullPath());
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

        $response->setCallback(function () use($files) {
            mb_http_output('pass');
            flush();
            ob_flush();
            $handle = popen('zip -r -j - ' . $files, 'r');
            while (!feof($handle)) {
                print(fread($handle, self::BUFFER_SIZE));
                flush();
                ob_flush();
            }
            pclose($handle);
        });

        return $response;

    }
}