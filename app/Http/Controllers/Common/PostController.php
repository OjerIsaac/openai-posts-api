<?php

namespace App\Http\Controllers\Common;

use App\Models\Post;
use App\Classes\Responsr;
use Illuminate\Http\Request;
use App\Services\PostService;
use Illuminate\Http\Response;
use Illuminate\Http\JsonResponse;
use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Http\Requests\CreatePostRequest;
use App\Http\Requests\UpdatePostRequest;

class PostController extends Controller
{
    public function __construct(private PostService $service) {}

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index(Request $request): JsonResponse
    {
        return Responsr::send("Posts fetched Successfully", [
            'data' =>
            PostResource::collection(
                $this->service->getPosts($request)
            )->response()->getData(true)
        ]);
    }

    /**
     * Create a post
     * @param CreatePostRequest $request
     * @return JsonResponse
     */
    public function store(CreatePostRequest $request): JsonResponse
    {
        return Responsr::send("Post generated successfully", ['data' => PostResource::make($this->service->createPost($request))], statusCode: Response::HTTP_CREATED);
    }

    public function destroy(Post $post)
    {
        $post->delete();
        return Responsr::send("Post deleted successfully", statusCode: Response::HTTP_OK);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  UpdatePostRequest  $request
     * @param Post $post
     * @return JsonResponse
     */
    public function update(UpdatePostRequest $request, Post $post): JsonResponse
    {
        $this->service->update($request, $post);
        return Responsr::send("Post updated Successfully", statusCode: Response::HTTP_OK);
    }
}
