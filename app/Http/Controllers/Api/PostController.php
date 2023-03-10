<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\StorePostRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $orderColumn = $request->input('order_column', 'id');
        $orderDirection = $request->input('order_direction', 'desc');

        if (!in_array($orderColumn, ['id', 'title'])) {
            $orderColumn = 'id';
        }

        if (!in_array($orderDirection, ['asc', 'desc'])) {
            $orderDirection = 'desc';
        }

        $filterable = ['id', 'title', 'content'];
        $filterableValues = array_filter($request->only($filterable));

        $posts = Post::with('category')
            ->when(count($filterableValues), function ($query) use ($filterableValues) {
                foreach ($filterableValues as $column => $value) {
                    $query->where($column, 'like', '%' . $value . '%');
                }
            })
            ->when($request->filled('category_id'), function ($query) use ($request) {
                $query->where('category_id', $request->category_id);
            })
            ->when($request->filled('global'), function ($query) use ($filterable, $request) {
                foreach ($filterable as $column) {
                    if ($column == $filterable[0]) {
                        $query->where($column, 'like', '%' . $request->global . '%');
                    } else {
                        $query->orWhere($column, 'like', '%' . $request->global . '%');
                    }
                }
            })
            ->orderBy($orderColumn, $orderDirection)
            ->paginate(10);

        return PostResource::collection($posts);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        $post = Post::create($request->validated());

        if ($request->hasFile('thumbnail')) {
            $filename = $request->file('thumbnail')->getClientOriginalName();
            info($filename);
        }

        return new PostResource($post);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        return new PostResource($post);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(StorePostRequest $request, Post $post)
    {
        $post->update($request->validated());

        return new PostResource($post);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        $post->delete();

        return response()->noContent();
    }
}
