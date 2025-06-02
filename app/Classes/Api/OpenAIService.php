<?php

namespace App\Classes\Api;

use Illuminate\Http\Client\Response;
use App\Classes\Request\InternalAppResponse;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class OpenAIService extends AbstractTransactionApi
{

    public function generatePost(string $topic)
    {
        $response = collect();
        $additionalData = collect();

        $data = [
            'model' => 'gpt-4.1',
            'messages' => [
                ['role' => 'system', 'content' => 'Generate a tweet-length social media post (max 280 chars).'],
                ['role' => 'user', 'content' => "Topic: $topic"]
            ],
        ];

        logger()->info('Sending payload to OpenAI:', $data);

        $this->post("/chat/completions", $data)
            ->onSuccess(function (Response $res) use (&$response, &$additionalData) {
                logger()->info('OpenAI API Success Response:', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                    'headers' => $res->headers(),
                ]);

                $apiResponse = $res->collect();

                $response = $response->merge([
                    'message'    => 'OpenAI request successful',
                    'status'     => 'success',
                    'statusCode' => SymfonyResponse::HTTP_OK,
                ]);

                $additionalData->put('openai', $apiResponse);
            })
            ->onError(function (Response $res) use (&$response) {
                logger()->error('OpenAI API Error Response:', [
                    'status' => $res->status(),
                    'body' => $res->body(),
                    'headers' => $res->headers(),
                ]);

                $response = $this->handleApiResponseError($res, $response);
            });

        return InternalAppResponse::send(
            $response->get('message'),
            $response->get('status'),
        )->with($additionalData->toArray());
    }

    /**
     * Setup Api Headers
     *
     * @return array
     */
    public function apiHeaders(): array
    {
        return [
            'Authorization' => 'Bearer ' . $this->apiKey(),
            'Content-Type'  => 'application/json',
        ];
    }

    private function apiKey(): string
    {
        return config('services.openAI.key');
    }

    /**
     * Get service base url
     * @return string
     */
    public function baseUrl(): string
    {
        return config('services.openAI.url');
    }
}
