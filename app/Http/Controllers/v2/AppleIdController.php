<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\Pages;
use Illuminate\Http\Request;

class AppleIdController extends Controller
{
    /* لیست اپل آیدی ها */
    public function index(Request $request)
    {
        $Page = Pages::find(3);
        $Comment = Comment::where('type','page')->where('post_id',4)->where('status',1)->orderby('id','DESC')->select('parent_id','name','text','created_at','id')->get();
        return view('site-v2.apple-id.index', compact('Page','Comment'));
    }
}
