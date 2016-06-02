<?php

use Pimcore\Model;

use Members\Controller\Action;
use Members\Model\Configuration;
use Members\Tool;

class Members_RequestController extends Action
{
    public function serveAction()
    {
        $this->disableLayout();

        $requestData = $this->getParam('d');

        if(empty($requestData))
        {
            $this->show404();
        }

        $base64 = $requestData . str_repeat('=', strlen($requestData) % 4);
        $data = base64_decode($base64);

        $fileInfo = json_decode($data);

        if( !is_array($fileInfo) )
        {
            $this->show404();
        }

        $dataToProcess = array();

        foreach( $fileInfo as $file )
        {
            $assetId = $file->f;
            $proxyId = $file->p;

            $asset = Model\Asset::getById( $assetId );

            if (!$asset instanceof Model\Asset)
            {
                continue;
            }

            if( $proxyId !== FALSE)
            {
                $object = Model\Object\AbstractObject::getById($proxyId);
                $restriction = Tool\Observer::isRestrictedObject( $object );

                if(  $restriction['section'] === Tool\Observer::SECTION_NOT_ALLOWED )
                {
                    continue;
                }
            }

            $dataToProcess[] = $asset;

        }

        if( count( $dataToProcess ) === 0 )
        {
            $this->show404();
        }
        else if( count( $dataToProcess ) == 1 )
        {
            $this->serveFile( $dataToProcess[0] );
        }
        else if( count( $dataToProcess ) > 1 )
        {
            $this->serveZip( $dataToProcess );
        }
        else
        {
            $this->show404();
        }
    }

    private function serveFile( Model\Asset $asset )
    {
        $size = $asset->getFileSize('noformatting');
        $quoted = sprintf('"%s"', addcslashes( basename($asset->getFileName()), '"\\') );

        header('Content-Description: File Transfer');
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename=' . $quoted);
        header('Content-Transfer-Encoding: binary');
        header('Connection: Keep-Alive');
        header('Expires: 0');
        header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
        header('Pragma: public');
        header('Content-Length: ' . $size);

        set_time_limit(0);

        $file = @fopen(PIMCORE_ASSET_DIRECTORY . $asset->getFullPath(),'rb');

        while(!feof($file))
        {
            print( @fread($file, 1024*8) );
            ob_flush();
            flush();
        }

        exit;
    }

    private function serveZip( $assets )
    {
        $fileName = 'package';

        header('Content-Type: application/zip');
        header('Content-disposition: attachment; filename="' . $fileName . '.zip"');
        header('Content-Transfer-Encoding: binary');

        mb_http_output('pass');

        ob_clean();
        flush();

        $files = '';

        foreach($assets as $asset)
        {
            $files .= '"'. PIMCORE_ASSET_DIRECTORY . $asset->getFullPath() .'" ';
        }

        $fp = popen('zip -r -j - ' . $files, 'r');

        $bufferSize = 8192;
        $buff = '';

        while( !feof($fp) )
        {
            $buff = fread($fp, $bufferSize);
            ob_flush();
            echo $buff;
        }

        pclose($fp);
        exit;
    }

    private function show404()
    {
        $response = $this->getResponse();
        $response->setHttpResponseCode(404);
        header("HTTP/1.0 404 Not Found");
        exit;
    }
}