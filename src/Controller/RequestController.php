<?php

namespace MembersBundle\Controller;

use MembersBundle\Configuration\Configuration;
use MembersBundle\Security\RestrictionUri;
use Pimcore\Model;

class RequestController extends AbstractController
{
    /**
     * @param null $hash
     */
    public function serveAction($hash = NULL)
    {            
        $requestData = $hash;

        if (empty($requestData)) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        /** @var RestrictionUri $restrictionUri */
        $restrictionUri = $this->container->get('members.security.restriction.uri');
        $dataToProcess = $restrictionUri->decodeAssetUrl($requestData);

        if ($dataToProcess === FALSE) {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }

        if (count($dataToProcess) == 1) {
            $this->serveFile($dataToProcess[0]);
        } else if (count($dataToProcess) > 1) {
            $this->serveZip($dataToProcess);
        } else {
            throw $this->createNotFoundException('invalid hash for asset request.');
        }
    }

    /**
     * @param Model\Asset $asset
     */
    private function serveFile(Model\Asset $asset)
    {
        /** @var Configuration $configuration */
        $configuration = $this->container->get('members.configuration');
        $hasLuceneSearch = $configuration->hasBundle('LuceneSearchBundle\LuceneSearchBundle');

        $forceDownload = TRUE;
        $contentType = 'application/octet-stream';

        if ($hasLuceneSearch) {
            /** @var \LuceneSearchBundle\Tool\CrawlerState $crawlerState */
            $crawlerState = $this->container->get('lucene_search.tool.crawler_state');
            if ($crawlerState->isLuceneSearchCrawler() && in_array($asset->getMimetype(), ['application/pdf'])) {
                $forceDownload = FALSE;
                $contentType = $asset->getMimetype();
            }
        }

        $size = $asset->getFileSize('noformatting');
        $quoted = sprintf('"%s"', addcslashes(basename($asset->getFileName()), '"\\'));

        if ($forceDownload === TRUE) {
            header('Content-Description: File Transfer');
            header('Content-Transfer-Encoding: binary');
            header('Content-Disposition: attachment; filename=' . $quoted);
        }

        header('Content-Type: ' . $contentType);
        header('Connection: Keep-Alive');
        header('Provider: Pimcore-Members');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);

        set_time_limit(0);

        $file = @fopen(rawurldecode(PIMCORE_ASSET_DIRECTORY . $asset->getFullPath()), 'rb');

        while (!feof($file)) {
            print(@fread($file, 1024 * 8));
            ob_flush();
            flush();
        }

        exit;
    }

    /**
     * @param $assets
     */
    private function serveZip($assets)
    {
        $fileName = 'package';

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . $fileName . '.zip"');
        header('Content-Transfer-Encoding: binary');

        mb_http_output('pass');

        ob_clean();
        flush();

        $files = '';

        /** @var Model\Asset $asset */
        foreach ($assets as $asset) {
            $files .= '"' . PIMCORE_ASSET_DIRECTORY . $asset->getFullPath() . '" ';
        }

        $fp = popen('zip -r -j - ' . $files, 'r');

        $bufferSize = 8192;
        $buff = '';

        while (!feof($fp)) {
            $buff = fread($fp, $bufferSize);
            ob_flush();
            echo $buff;
        }

        pclose($fp);
        exit;
    }
}