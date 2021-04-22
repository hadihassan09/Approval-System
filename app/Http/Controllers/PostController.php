<?php

namespace App\Http\Controllers;

use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Validator;

class PostController extends Controller
{
    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        //
    }

    /**
     * Display a listing of the resource.
     *
     * @return JsonResponse
     */
    public function index()
    {
        $posts = Post::where('isApproved', true)->get();
        return response()->json([
            'posts' => $posts,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  Request  $request
     * @return JsonResponse
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $post = Post::create([
            'description' => $request->description,
            'user_id' => Auth::user()->id
        ]);

        //We can also approve users (admin user) posts automatically
//        if (Auth::user()->can('system-admin')) {
//            $post->isApproved=true;
//            $post->save();
//        }

        return response()->json([
            'success' => true,
            'status' => 'pending'
        ]);
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function show($id)
    {
        $post = Post::find($id);
        if($post && $post->isApproved) {
            return response()->json([
                'post' => $post,
                'status' => 'post approved'
            ]);
        }
        elseif ($post)
            return response()->json([
                'post' => null,
                'status' => 'post not approved'
            ]);
        return response()->json(['error'=>'id does not exist'], 404);
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  Request  $request
     * @param  int  $id
     * @return JsonResponse
     */
    public function update(Request $request, $id)
    {
        try {
            $post = Post::findOrFail($id);
            if ($request->user()->cannot('post-owner', $post)) {
                return response()->json(['error'=>'Post Not Owned by User'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Post Not Found'], 404);
        }

        $validator = Validator::make($request->all(), [
            'description' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json(['errors'=>$validator->errors()], 422);
        }

        $post->description =  $request->description;
        $post->save();

        return response()->json([
            'success' => true
        ]);
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return JsonResponse
     */
    public function destroy($id)
    {
        try {
            $post = Post::findOrFail($id);
            if (Auth::user()->cannot('post-owner', $post) && Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'Post Not Owned by User'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Post Not Found'], 404);
        }

        $post->delete();
        return response()->json([
            'success' => 'true',
            'message' => 'Post Deleted Successfully'
        ]);
    }

    public function approvePost($id){
        try {
            $post = Post::findOrFail($id);
            if (Auth::user()->cannot('system-admin')) {
                return response()->json(['error'=>'User cannot validate posts, invalid access'], 403);
            }
        }catch (\Exception $e){
            return response()->json(['error'=>'Post Not Found'], 404);
        }

        $post->isApproved = true;
        $post->save();

        return response()->json([
            'success' => 'true',
            'message' => 'Post Approved Successfully'
        ]);
    }

    public function unapprovedPosts(){
        if (Auth::user()->cannot('system-admin')) {
            return response()->json(['error'=>'Invalid access'], 403);
        }
        $posts = Post::where('isApproved', false)->get();
        return response()->json([
            'posts' => $posts,
        ]);
    }
}
