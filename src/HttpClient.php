<?php

declare(strict_types=1);

namespace madpilot78\FreeBoxPHP;

use GuzzleHttp\Exception\ClientException;
use GuzzleHttp\ClientInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;
use madpilot78\FreeBoxPHP\Exception\ApiAuthException;
use madpilot78\FreeBoxPHP\Exception\ApiErrorException;
use madpilot78\FreeBoxPHP\Exception\NetworkErrorException;

class HttpClient implements HttpClientInterface
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
    private function bodyToJson(ResponseInterface $response, array $reqResult = []): array
    {
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

            if ($reqResult[0] !== '') {
                foreach ($reqResult as $req) {
                    if (!array_key_exists($req, $json['result'])) {
                        throw new ApiErrorException(ApiErrorException::FIELD_MISSING . ': ' . $req);
                    }
                }
            }

            // Only return the result
            $json = $json['result'];
        }

        return $json;
    }

    public function get(string $url, array $required = [], array $options = []): array
    {
        return $this->doRequest(__FUNCTION__, $url, $required, $options);
    }

    public function post(string $url, array $required = [], array $options = []): array
    {
        return $this->doRequest(__FUNCTION__, $url, $required, $options);
    }

    public function put(string $url, array $required = [], array $options = []): array
    {
        return $this->doRequest(__FUNCTION__, $url, $required, $options);
    }

    public function delete(string $url, array $options = []): array
    {
        return $this->doRequest(__FUNCTION__, $url, [], $options);
    }

    /**
     * Wrap Guzzle client.
     *
     * If first argument is an Array grab it for own use as required parameters in response.
     * Otherwise it is expected to be the UR>L argument to Guzzle.
     *
     * @throws ApiAuthException
     * @throws ApiErrorException
     */
    private function doRequest(string $method, string $url, array $reqResults = [], array $options = []): array
    {
        $this->logger->debug('FreeBoxPHP performing request', compact('method', 'url', 'options', 'reqResults'));

        try {
            $response = $this->client->request($method, $url, $options);
            $this->logger->debug('FreeBoxPHP got response', compact('response'));
        } catch (ClientException $e) {
            $response = $e->getResponse();
            $statusCode = $response->getStatusCode();
            $this->logger->warning('FreeBoxPHP got ClientException', compact('response', 'statusCode'));

            switch ($statusCode) {
                case 403:
                    $error = $this->bodyToJson($response);
                    throw new ApiAuthException($error['msg'] ?? 'Unknown error', $statusCode);

                case 404:
                    $decoded = $this->bodyToJson($response);
                    throw new ApiErrorException('', $decoded, $statusCode);

                default:
                    throw new NetworkErrorException($e->getMessage(), $e->getCode(), $e);
            }
        }

        $this->checkStatusCode($response);

        return $this->bodyToJson($response, $reqResults);
    }
}
