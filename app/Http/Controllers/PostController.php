<?php

namespace App\Http\Controllers;

use App\Models\Post;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
class PostController extends Controller implements HasMiddleware
{

    public static function middleware(): array
    {
        return [
            new Middleware('auth', except: ['index','store']),
        ];
    }
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
        $posts = Post::latest()->paginate(6); // pagination

        return view('posts.index', ['posts' => $posts]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {

        

        //dd("fd");

        $fields = $request->validate([
            'title' => ['required','max:255'],
            'body' => ['required'],
            'image' => ['nullable', 'file', 'max:3000', 'mimes:png,jpg,webp']
        ]);
        $path = null;
        if($request->hasFile('image')){
            $file = $request->file('image');


          $path =  Storage::disk('public')->put('posts_images' , $file);
        // Generate a unique filename with extension
        //$filename = time() . '-' . $file->getClientOriginalName();

        // Store the file in the 'posts_images' directory within the 'public' disk
            //$path = $file->move(public_path('\storage\posts_images'), $filename);

        //dd($path);
        }


        Auth::user()->posts()->create([
            'title' => $request->title,
            'body' => $request->body,
            'image' => $path
        ]);

        return back()->with('Success', 'Your post was created');
    }

    /**
     * Display the specified resource.
     */
    public function show(Post $post)
    {
        //
        return view('posts.show', ['post' => $post]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Post $post)
    {
        //
        Gate::authorize('modify', $post);
        return view('posts.edit', ['posts' => $post]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Post $post)
    {
        Gate::authorize('modify', $post);
        $fields = $request->validate([
            'title' => ['required','max:255'],
            'body' => ['required']
        ]);
        
        $post->update($fields);

        return redirect()->route('dashboard')->with('Success', 'Your post was created');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Post $post)
    {
        Gate::authorize('modify', $post);
        $post->delete();

        // check if image exits
        if($post->image){
            Storage::disk('public')->delete($post->image);
        }


        return back()->with('delete', 'Your post was deleted');
    }
}
