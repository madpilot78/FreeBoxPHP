<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

use GuzzleHttp\Exception\ClientException;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use madpilot78\FreeBoxPHP\Exception\ApiAuthException;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\NetworkErrorException;

class HttpClient
{
    public function __construct(
        private ClientInterface $client,
        private LoggerInterface $logger,
    ) {}

    /**
     * @throws NetworkErrorException
     */
    private function checkStatusCode(ResponseInterface $response): void
    {
        $code = $response->getStatusCode();

        if ($code != 200) {
            throw new NetworkErrorException(
                'Unexpected HTTP status code (' . $code . '): ' . $response->getReasonPhrase(),
                $code,
            );
        }
    }

    /**
     * @throws ApiErrorException
     */
    private function bodyToJson(ResponseInterface $response, array $reqResult = [], bool $checkStatus = true): array
    {
        if ($checkStatus) {
            $this->checkStatusCode($response);
        }

        $rawBody = (string) $response->getBody();
        if (!json_validate($rawBody)) {
            throw new ApiErrorException('Invalid JSON in body');
        }

        $json = json_decode($rawBody, true);

        if (!empty($reqResult)) {
            if (!array_key_exists('success', $json) || !$json['success']) {
                throw new ApiErrorException('', $json);
            }

            if (!array_key_exists('result', $json)) {
                throw new ApiErrorException(ApiErrorException::RESULT_MISSING);
            }

            foreach ($reqResult as $req) {
                if (!array_key_exists($req, $json['result'])) {
                    throw new ApiErrorException(ApiErrorException::FIELD_MISSING . ': ' . $req);
                }
            }

            // Only return the result
            $json = $json['result'];
        }

        return $json;
    }

    /**
     * Wrap Guzzle client.
     *
     * If first argument is an Array grab it for own use. Otherwise it
     * is expected to be a method or an URL.
     *
     * @throws ApiAuthException
     * @throws ClientException
     */
    public function __call(string $name, array $arguments): array
    {
        $reqResults = [];
        if (is_array($arguments[0])) {
            $reqResults = array_shift($arguments);
        }

        $this->logger->debug('FreeBoxPHP performing request', compact('name', 'arguments', 'reqResults'));

        try {
            $response = $this->client->$name(...$arguments);
            $this->logger->debug('FreeBoxPHP got response', compact('response'));
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $this->logger->warning('FreeBoxPHP got ClientException', compact('response', 'statusCode'));

            switch ($statusCode) {
                case 403:
                    $error = $this->bodyToJson($response, checkStatus: false);
                    throw new ApiAuthException($error['msg'] ?? 'Unknown error', $statusCode, $e);
                    break;

                default:
                    throw $e;
                    break;
            }
        }

        $this->checkStatusCode($response);

        return $this->bodyToJson($response, $reqResults, false);
    }
}
