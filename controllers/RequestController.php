<?php

use Pimcore\Model;

use Members\Controller\Action;
use Members\Tool;

class Members_RequestController extends Action
{
    /**
     *
     */
    public function serveAction()
    {
        $this->disableLayout();

        $requestData = $this->getParam('d');

        if (empty($requestData)) {
            $this->show404();
        }

        $dataToProcess = Tool\UrlServant::decodeAssetUrl($requestData);

        if($dataToProcess === FALSE) {
            $this->show404();
        }

        if (count($dataToProcess) === 0) {
            $this->show404();
        } else if (count($dataToProcess) == 1) {
            $this->serveFile($dataToProcess[0]);
        } else if (count($dataToProcess) > 1) {
            $this->serveZip($dataToProcess);
        } else {
            $this->show404();
        }
    }

    /**
     * @param Model\Asset $asset
     */
    private function serveFile(Model\Asset $asset)
    {
        $forceDownload = TRUE;
        $contentType = 'application/octet-stream';

        $hasLuceneSearch = \Pimcore\ExtensionManager::isEnabled('plugin', 'LuceneSearch');

        if($hasLuceneSearch) {
            if(\LuceneSearch\Tool\Request::isLuceneSearchCrawler() && in_array($asset->getMimetype(), ['application/pdf'])) {
                $forceDownload = FALSE;
                $contentType = $asset->getMimetype();
            }
        }

        $size = $asset->getFileSize('noformatting');
        $quoted = sprintf('"%s"', addcslashes(basename($asset->getFileName()), '"\\'));

        if($forceDownload === TRUE) {
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

        $file = @fopen(PIMCORE_ASSET_DIRECTORY . $asset->getFullPath(), 'rb');

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

    /**
     *
     */
    private function show404()
    {
        $response = $this->getResponse();
        $response->setHttpResponseCode(404);
        header("HTTP/1.0 404 Not Found");
        exit;
    }
}