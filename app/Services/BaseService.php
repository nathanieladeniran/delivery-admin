<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Http\Client\Response;
use InvalidArgumentException;

abstract class BaseService
{
    protected $response;
    protected $client;
    protected $headers = [];
    public $response_status = 0;
    protected $timeout = 60;

    abstract protected function baseUri();

    public function __construct()
    {
        $this->client = Http::baseUrl($this->baseUri())
            ->timeout($this->timeout)
            ->withHeaders($this->headers);
    }

    public function makeRequest(string $method, string $uri, array $options = [])
    {
        $this->checkValidRequestMethod($method);

        if (!empty($this->headers)) {
            $options['headers'] = array_merge($this->headers, $options['headers'] ?? []);
        }

        try {
            // Check if the request involves file uploads
            if ($method === 'POST' && isset($options['file'])) {
                $response = $this->client->attach(
                    $options['file']['name'],
                    $options['file']['contents'],
                    $options['file']['filename']
                )->post($uri, $options['data'] ?? []);
            } else {
                $response = $this->client->{strtolower($method)}($uri, $options['data'] ?? []);
            }

            return $this->renderResponse($response);
        } catch (\Exception $e) {
            return $this->renderExceptionResponse($e);
        }
    }

    protected function checkValidRequestMethod($method)
    {
        if (!$this->isValidRequestMethod($method)) {
            throw new InvalidArgumentException("{$method} is not a valid request type");
        }
    }

    protected function isValidRequestMethod($method)
    {
        $valid_methods = ['GET', 'POST', 'PUT', 'DELETE', 'PATCH'];

        return in_array(strtoupper($method), $valid_methods);
    }

    protected function setHeaders(array $headers)
    {
        $this->headers = array_merge($this->headers, $headers);
        $this->client = $this->client->withHeaders($this->headers);
    }

    protected function renderResponse(Response $response)
    {
        $this->response_status = $response->status();

        return [
            'status' => $this->response_status,
            'data' => $response->json(),
        ];
    }

    public function renderExceptionResponse($exception)
    {
        $this->response_status = $exception->getCode();

        return [
            'status' => $this->response_status,
            'message' => $exception->getMessage(),
            'error' => method_exists($exception, 'response') && $exception->response()
                ? $exception->response()->json()
                : $exception->getMessage(),
        ];
    }

    public function getClient()
    {
        return $this->client;
    }

    public function getResponse()
    {
        return $this->response;
    }

    public function __call($method, $arguments)
    {
        $this->checkValidRequestMethod($method);

        return $this->makeRequest($method, ...$arguments);
    }
}
