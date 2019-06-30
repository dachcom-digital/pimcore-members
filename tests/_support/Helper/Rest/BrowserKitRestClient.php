<?php

namespace DachcomBundle\Test\Helper\Rest;

use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use Symfony\Component\BrowserKit\Request as BrowserKitRequest;
use Symfony\Component\BrowserKit\Response as BrowserKitResponse;

class BrowserKitRestClient extends \Pimcore\Tests\Rest\BrowserKitRestClient
{
    /**
     * @inheritDoc
     */
    public function getResponse($method, $uri, array $parameters = [], array $files = [], array $server = [], $content = null)
    {
        $uri = $this->prepareUri($uri);
        $parameters = $this->prepareParameters($parameters);
        $server = $this->prepareHeaders($server);

        if (count($parameters) > 0) {
            $query = http_build_query($parameters);

            if (false === strpos($uri, '?')) {
                $uri .= '?' . $query;
            } else {
                $uri .= '&' . $query;
            }
        }

        codecept_debug('[BrowserKitRestClient] Requesting URI ' . $uri);

        $this->client->request($method, $uri, $parameters, $files, $server, $content);

        /** @var BrowserKitRequest $browserKitRequest */
        $browserKitRequest = $this->client->getInternalRequest();

        /** @var BrowserKitResponse $response */
        $browserKitResponse = $this->client->getInternalResponse();

        $headers = $browserKitRequest->getServer();
        if (isset($headers['HTTPS'])) {
            $headers['HTTPS'] = (string) $headers['HTTPS'];
        }

        $request = new Request(
            $browserKitRequest->getMethod(),
            $browserKitRequest->getUri(),
            $headers,
            $browserKitRequest->getContent()
        );

        $response = new Response(
            $browserKitResponse->getStatus(),
            $browserKitResponse->getHeaders(),
            $browserKitResponse->getContent()
        );

        $this->lastRequest = $request;
        $this->lastResponse = $response;

        return $response;
    }
}