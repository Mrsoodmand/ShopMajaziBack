<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\Models\Comment;
use App\Models\GoogleVoice;
use App\Models\Pages;
use Illuminate\Http\Request;

class GoogleVoiceController extends Controller
{
    /* لیست اپل آیدی ها */
    public function index(Request $request)
    {
        $Page = Pages::find(4);
        $GoogleVoices = GoogleVoice::where('status',0)->select('id','number')->get();
        $Comment = Comment::where('type','page')->where('post_id',6)->where('status',1)->orderby('id','DESC')->select('parent_id','name','text','created_at','id')->get();
        return view('site-v2.google-voice.index',compact('Page','GoogleVoices','Comment'));
    }
}
