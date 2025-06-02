<?php

namespace App\Services;

use App\Models\Post;
use App\Classes\Api\OpenAIService;
use Illuminate\Http\Request;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostService
{
    /**
     * OpenAI Service function to generate a content
     * @param CreatePostRequest $request
     */
    public function createPost(CreatePostRequest $request)
    {
        $response = (new OpenAIService())->generatePost($request->validated('topic'));

        $openAiData = $response->getAdditionalData()->get('openai');
        // dd($openAiData['id']);

        $content   = $openAiData['choices'][0]['message']['content'] ?? null;
        $openaiId  = $openAiData['id'] ?? null;
        $topic     = $request->validated('topic');

        $post = Post::create([
            'openai_id' => $openaiId,
            'topic'     => $topic,
            'content'   => $content,
        ]);

        return $post;
    }

    /**
     * OpenAI Service function to get all posts
     * @param Request $request
     * @return mixed
     */
    public function getPosts(Request $request): mixed
    {
        return Post::query()->latest()->get();
    }

    public function update(UpdatePostRequest $request, Post $post)
    {
        $validated = $request->validated();

        $updateData = [];

        if (isset($validated['topic'])) {
            $updateData['topic'] = $validated['topic'];
        }

        if (isset($validated['content'])) {
            $updateData['content'] = $validated['content'];
        } elseif (isset($validated['topic'])) {
            $response = (new OpenAIService())->generatePost($validated['topic']);

            $openAiData = $response->getAdditionalData()->get('openai');

            $updateData['content']   = $openAiData['choices'][0]['message']['content'] ?? $post->content;
            $updateData['openai_id'] = $openAiData['id'] ?? $post->openai_id;
        }

        $post->update($updateData);

        return $post;
    }
}
