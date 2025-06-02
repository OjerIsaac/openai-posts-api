<?php

namespace App\Classes\Api;

use Illuminate\Config\Repository;
use Illuminate\Contracts\Foundation\{Application};
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Support\Collection;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

abstract class AbstractTransactionApi
{
    /**
     * Holds the API response
     *
     * @var Response
     */
    private $response;

    /**
     * Setup Api Headers
     *
     * @return array
     */
    public abstract function apiHeaders(): array;

    /**
     *
     * Setup Api BaseUrl
     * @return string
     */
    public abstract function baseUrl(): string;

    /**
     * Send an API Get request
     *
     * @param string $endpoint
     * @param array|null $params
     * @return AbstractTransactionApi
     */
    public function get(string $endpoint, ?array $params = null): AbstractTransactionApi
    {
        $this->response = $this->prepareHttpFacadeWithHeaders()
            ->get($this->resolveEndpoint($endpoint), $params);

        return $this;
    }

    /**
     * Send an API Post request
     *
     * @param string $endpoint
     * @param array $data
     * @return AbstractTransactionApi
     */
    public function post(string $endpoint, array $data = [], array $attachment = []): AbstractTransactionApi
    {
        $request = $this->prepareHttpFacadeWithHeaders();

        if (collect($attachment)->isNotEmpty()) {
            $request->attach($attachment);
        }

        $this->response = $request->post($this->resolveEndpoint($endpoint), $data);

        return $this;
    }

    /**
     * Send an API Put request
     *
     * @param string $endpoint
     * @param array $data
     * @return AbstractTransactionApi
     */
    public function put(string $endpoint, array $data = []): AbstractTransactionApi
    {
        $this->response = $this->prepareHttpFacadeWithHeaders()
            ->put($this->resolveEndpoint($endpoint), $data);

        return $this;
    }

    /**
     * Send an API Patch request
     *
     * @param string $endpoint
     * @param array $data
     * @return AbstractTransactionApi
     */
    public function patch(string $endpoint, array $data = []): AbstractTransactionApi
    {
        $this->response = $this->prepareHttpFacadeWithHeaders()
            ->patch($this->resolveEndpoint($endpoint), $data);

        return $this;
    }

    /**
     * Send an API Delete request
     *
     * @param string $endpoint
     * @param array $params
     * @return AbstractTransactionApi
     */
    public function delete(string $endpoint, array $params = []): AbstractTransactionApi
    {
        $this->response = $this->prepareHttpFacadeWithHeaders()
            ->delete($this->resolveEndpoint($endpoint), $params);

        return $this;
    }

    /**
     * Check if API Request response is successful
     *
     * @return bool
     */
    public function isSuccessful(): bool
    {
        return $this->response->successful();
    }

    /**
     * Check if API Request response code was "OK".
     *
     * @return bool
     */
    public function ok(): bool
    {
        return $this->response->ok();
    }

    /**
     * Get the API Request response status
     *
     * @return integer
     */
    public function status(): int
    {
        return $this->response->status();
    }

    /**
     * Check if API Request response failed
     *
     * @return integer
     */
    public function failed(): bool
    {
        return $this->response->failed();
    }

    /**
     * Perform an action on Successful response
     *
     * @param callable $callback
     * @return self
     */
    public function onSuccess(callable $callback)
    {
        if ($this->isSuccessful()) {
            $callback($this->response);
        }

        return $this;
    }

    /**
     * Perform an action on error response
     *
     * @param callable $callback
     * @return self
     */
    public function onError(callable $callback)
    {
        if ($this->failed()) {
            $callback($this->response);
        }

        return $this;
    }

    /**
     * Get the body of the request
     *
     * @param string|null $key
     * @return Collection
     */
    public function body($key = null): Collection
    {
        return $this->response->collect($key);
    }

    /**
     * Resolve endpoint, combining the passed endpoint with the base url
     *
     * @param string $endpoint
     * @return string
     */
    public function resolveEndpoint(string $endpoint): string
    {
        $endpointBase = $this->baseUrl();
        if ($endpointBase && !str_ends_with($endpointBase, '/')) {
            $endpointBase .= '/';
        }

        return $endpointBase . trim($endpoint, '/');
    }

    /**
     * Return instance of Http Facade with headers
     * @return PendingRequest
     */
    private function prepareHttpFacadeWithHeaders(): PendingRequest
    {
        return Http::withHeaders($this->apiHeaders());
    }

    /**
     * Error Handler
     *
     * @param Response $res
     * @param Collection $response
     * @return Collection
     */
    public function handleApiResponseError(Response $res, Collection $response): Collection
    {
        $apiResponse = $res->collect();
        $response = $response->merge([
            'message'       => $this->getFirstErrorMessageInResponse($apiResponse),
            'status'        => 'error',
            'statusCode'    => is_string($apiResponse->get('status')) ? SymfonyResponse::HTTP_FOUND : $apiResponse->get('status'),
        ]);

        return $response;
    }

    /**
     * Get the first error message in Machnet error response payload
     *
     * @param Collection $responsePayload
     * @return string
     */
    private function getFirstErrorMessageInResponse(Collection $responsePayload): string
    {
        $firstError = 'Error sending API request. Check configuration';

        if ($responsePayload->has('errors')) {
            $firstError = collect($responsePayload->get('errors'))->first();
            $firstError = is_array($firstError) && isset($firstError['message']) ? $firstError['message'] : $firstError;
        } else {
            if ($responsePayload->has('message')) {
                $firstError = $responsePayload->get('message');
            }
        }

        return $firstError;
    }
}
