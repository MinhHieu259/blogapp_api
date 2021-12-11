<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class PostsController extends Controller
{
    public function create(Request $request)
    {
        $post = new Post();
        $post->user_id = Auth::user()->id;
        $post->desc = $request->desc;

        // Kiểm tra post có ảnh
        if($request->photo != ''){
            // tạo ảnh k trùng
            $photo = time().'.jpg';
            file_put_contents('storage/posts/'.$photo,base64_decode($request->photo));
            $post->photo = $photo;
        }
        $post->save();
        $post->user();
        return response()->json([
            'success' => true,
            'message' => 'posted',
            'post' => $post
        ]);
    }
    public function update(Request $request){
        $post = Post::find($request->id);
        // check if user editting his own post
        if(Auth::user()->id != $post->user_id){
            return response()->json([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
        }
        $post->desc = $request->desc;
        $post->update();
        return response()->json([
            'success' => true,
            'message' => 'post edited'
        ]);
    }
    public function delete(Request $request){
        $post = Post::find($request->id);
        // check if user editting his own post
        if(Auth::user()->id != $post->user_id){
            return response()->json([
                'success' => false,
                'message' => 'unauthorized access'
            ]);
        }
        // Check if post has photo to delete
        if($post->photo != ''){
            Storage::delete('public/posts/'.$post->photo);
        }
        $post->delete();
        return response()->json([
            'success' => true,
            'message' => 'post deleted'
        ]);
    }
    public function posts()
    {
        $posts = Post::orderBy('id','desc')->get();
        foreach ($posts as $post) {
            // get user of post
            $post->user;
            // comments count
            $post['commentsCount'] = count($post->comments);
            // Like count
            $post['likeCount'] = count($post->likes);
            // check if user like his post
            $post['selfLike'] = false;
            foreach ($post->likes as $like) {
                if($like->user_id == Auth::user()->id){
                    $post['selfLike'] = true;
                }
            }
        }
        return response()->json([
            'success' => true,
            'post' => $posts
        ]);
    }
}
