<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Editor;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Http\Response;
use Illuminate\Support\Str;

class PostController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $editor = $this->currentEditor($request);

        return PostResource::collection($editor->posts()->latest()->get());
    }

    public function store(StorePostRequest $request): PostResource
    {
        $editor = $this->currentEditor($request);

        $this->authorize('create', Post::class);

        $validated = $request->validated();
        $title = is_string($validated['title']) ? $validated['title'] : '';

        $post = $editor->posts()->create([
            'title' => $title,
            'slug' => $this->generateUniqueSlug($title),
            'excerpt' => $validated['excerpt'] ?? null,
            'body' => $validated['body'],
            'cover_image_url' => $validated['cover_image_url'] ?? null,
            'published_at' => ($validated['published'] ?? false) ? now() : null,
        ]);

        return new PostResource($post);
    }

    public function show(Post $post): PostResource
    {
        return new PostResource($post);
    }

    public function update(UpdatePostRequest $request, Post $post): PostResource
    {
        $this->authorize('update', $post);

        $validated = $request->validated();

        $data = array_intersect_key($validated, array_flip(['title', 'excerpt', 'body', 'cover_image_url']));

        if (array_key_exists('published', $validated)) {
            $data['published_at'] = $validated['published'] ? ($post->published_at ?? now()) : null;
        }

        $post->update($data);

        return new PostResource($post);
    }

    public function destroy(Post $post): Response
    {
        $this->authorize('delete', $post);

        $post->delete();

        return response()->noContent();
    }

    public function publish(Post $post): PostResource
    {
        $this->authorize('update', $post);

        $post->update(['published_at' => $post->published_at ?? now()]);

        return new PostResource($post);
    }

    public function unpublish(Post $post): PostResource
    {
        $this->authorize('update', $post);

        $post->update(['published_at' => null]);

        return new PostResource($post);
    }

    private function currentEditor(Request $request): Editor
    {
        $editor = $request->user();

        if (! $editor instanceof Editor) {
            abort(403);
        }

        return $editor;
    }

    private function generateUniqueSlug(string $title): string
    {
        $base = Str::slug($title);
        $slug = $base;
        $suffix = 2;

        while (Post::where('slug', $slug)->exists()) {
            $slug = $base.'-'.$suffix;
            $suffix++;
        }

        return $slug;
    }
}
