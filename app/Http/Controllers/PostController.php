<?php

namespace App\Http\Controllers;

use App\Http\Requests\StorePostRequest;
use App\Http\Requests\UpdatePostRequest;
use App\Http\Resources\CommentResource;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $sortBy = $request->query('sortBy', 'recent');
        if($sortBy == 'recent'){
            $sortBy = 'created_at';
        }else{
            $sortBy = 'rating';
        }
        $location = $request->query('location');
        $creatorId = $request->query('creatorId');
        $page = $request->query('page', 1);
        $limit = $request->query('limit', 10);
        $offset = ($page - 1) * $limit;
        if($sortBy == 'rating'){
            $query = Post::with('images', 'comments')->orderBy('rating', 'desc');
        }else{
            $query = Post::with('images', 'comments')->orderBy($sortBy);
        }
        if ($location) {
            $query->where('location', 'like', "%$location%");
        }
        if ($creatorId) {
            $query->where('user_id', $creatorId);
        }
        $total = $query->count();
        $posts = $query->offset($offset)->limit($limit)->get();

        return response()->json(['data' => PostResource::collection($posts), 'total' => $total, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($total / $limit)], 200);
    }


    public function getMyPosts(Request $request)
    {
        $user = $request->user();
        $posts = Post::where('user_id', $user->id)->with('images', 'comments')->get();

        return response()->json(['data' => PostResource::collection($posts)], 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StorePostRequest $request)
    {
        //
        $validated = $request->validated();
        $post = Post::create(array_merge($validated, ['user_id' => $request->user()->id]));

        if ($request->hasFile('images')) {
            foreach ($request->file('images') as $image) {
                $file_path = 'posts/' . $post->id;
                $image->store($file_path );
                $post->images()->create([
                    'url' => "{$file_path}/" .$image->hashName(),
                    'disk' => config('filesystems.default'),
                ]);
               
            }
        }
        if($request->input('peopleTagged')){
            foreach ($request->input('peopleTagged') as $user) {
                $post->users()->attach($user);
            }
        }
        $post->load('users');
        $post->load('images');

        return response()->json(["data" => $post], 201);
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
        $post->load('images');
        $post->load('comments');
        $post->load('users');   
        
     
        return response()->json(["data" => new PostResource($post)], 200);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdatePostRequest $request, Post $post)
    {
        //
        $validated = $request->validated();
        $post->update($validated);
        if($request->input('peopleTagged')){
            $post->users()->sync($request->input('peopleTagged'));
        }
        $post->load('users');
        $post->load('images');
        $post->load('comments');

        return response()->json(["data" => $post], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        //
        $post->delete();

        return response()->json(null, 204);
    }


    public function saveComment(Request $request, Post $post)
    {
        $request->validate([
            'content' => 'required|string',
        ]);

        $comment = $post->comments()->create([
            'user_id' => $request->user()->id,
            'content' => $request->input('content'),
            'parent_id' => $request->input('parent_id'),
        ]);

        return response()->json(['data' => new CommentResource($comment)], 201);
    }

    public function getComments(Post $post)
    {
        $comments = $post->comments()->where('parent_id', null)->with('user')->get();

        return response()->json(['data' => CommentResource::collection($comments)], 200);
    }

    public function getChildComments(Comment $comment)
    {
        $comments = $comment->children()->with('user')->get();

        return response()->json(['data' => CommentResource::collection($comments)], 200);
    }

    public function saveRating(Request $request, Post $post)
    {
        $request->validate([
            'rating' => 'required|integer|between:1,5',
        ]);

        $post->ratings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['rating' => $request->input('rating')]
        );

        $averageRating = $post->ratings()->avg('rating');
        $post->update(['rating' => $averageRating]);

        return response()->json( ["data" => ['averageRating' => round($averageRating, 2)] , "message" => [$averageRating]], 200);
    }

    public function saveLike(Request $request, Post $post)
    {
        $post->likes()->updateOrCreate(
            ['user_id' => $request->user()->id]
        );

        return response()->json(["data" => ['likes' => $post->likes()->count()]], 200);
    }

    public function removeLike(Request $request, Post $post)
    {
        $post->likes()->where('user_id', $request->user()->id)->delete();

        return response()->json(["data" => ['likes' => $post->likes()->count()]], 200);
    }

    public function getUserPhotoStatus(Request $request, Post $post)
    {
        $isLiked = $post->likes()->where('user_id', $request->user()->id)->exists();
        $rating = $post->ratings()->where('user_id', $request->user()->id)->value('rating');

        return response()->json(["data" => ['isLiked' => $isLiked, 'rating' => $rating]], 200);
    }

    public function getLikedPosts(Request $request)
    {
        $user = $request->user();
        $posts = $user->likedPosts()->with('images', 'comments')->get();

        return response()->json(['data' => PostResource::collection($posts)], 200);
    }

    public function search(Request $request)
    {
        $search = $request->input('search');
        $location = $request->input('location');
        $creatorId = $request->input('creatorId');
        $page = $request->input('page', 1); 
        $limit = $request->input('limit', 10);
        $offset = ($page - 1) * $limit;
        $query = Post::with('images', 'comments');
        if ($search) {
            $query->where('title', 'like', "%$search%")->orWhere('caption', 'like', "%$search%");
        }
        if ($location) {
            $query->where('location', 'like', "%$location%");
        }
        if ($creatorId) {
            $query->where('user_id', $creatorId);
        }
        $total = $query->count();
        $posts = $query->offset($offset)->limit($limit)->get();

        return response()->json(['data' => PostResource::collection($posts), 'total' => $total, 'page' => $page, 'limit' => $limit, 'totalPages' => ceil($total / $limit)], 200);
    }
}
