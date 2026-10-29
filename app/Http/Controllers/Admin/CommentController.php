<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\Comment;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class CommentController extends Controller
{
    /* لیست کامنت ها */
    public function Comments(Request $request)
    {
        $list = Comment::orderby('id', 'DESC')->where('parent_id', '0');
        if ($request->has('type') && $request->input('type') != 0) {
            $list->where('type', $request->input('type'));
        }
        if ($request->has('search') && $request->input('type') != ' ' && $request->input('type') != null) {
            $list->where('email', 'LIKE', '%' . $request->input('email') . '%');
        }
        $List = $list->paginate(50);
        $jdf = new Jdf();
        return view('admin-v2.comment.index', compact('List', 'jdf'));
    }


    public function ChangeCommentStatus(Request $request)
    {
        $Validate = Validator::make($request->all(), [
            'id' => 'required',
            'status' => 'required'
        ], [
            'id.required' => 'لطفا شناسه را وارد نمایید.',
            'status.required' => 'لطفا وضعیت را وارد نمایید.',
        ]);
        if ($Validate->fails()) {
            return response()->json(['errors' => $Validate->errors()->all()], 400);
        }
        try {
            $Change = Comment::find($request->input('id'));
            $Change->status = $request->input('status');
            $Change->save();

            if ($Change->status == 1) {
                $Html = '<div class="badge badge-light-success">تایید شده.</div>';
            } elseif ($Change->status == 0) {
                $Html = ' <div class="badge badge-light-info">درانتظار تایید.</div>';
            } else {
                $Html = '<div class="badge badge-light-danger">عدم تایید.</div>';
            }

            return response()->json(['msg' => 'وضعیت با موفقیت تغییر کرد', 'Html' => $Html], 200);
        } catch (\Exception $exception) {
            return response()->json(['errors' => [$exception->getMessage()]], 400);
        }
    }


    public function comment_data(Request $request)
    {
        $single = Comment::find($request->input('id'));
        $Answer = Comment::where('parent_id', $single->id)->first();
        $html = view('admin-v2.comment.modal', compact('single', 'Answer'))->render();

        return response(['html' => $html], 200);
    }


    public function AnswerComment(Request $request)
    {
        $Valdiate = Validator::make($request->all(), [
            'comment' => 'required',
            'post_id' => 'required',
        ], [
            'comment.required' => 'لطفا پاسخ را وارد کنید',
            'post_id.required' => 'آیدی پست را وارد کنید.',

        ]);
        if ($Valdiate->fails()) {
            return response()->json(['error' => $Valdiate->errors()->all()], 400);
        }
        try {
            $CommentDetail = Comment::find($request->input('post_id'));
            if ($request->input('id') != 0) {
                $Comment = Comment::find($request->input('id'));
            } else {
                $Comment = new Comment();
            }
            $Comment->text = $request->input('comment');
            $Comment->type = 'answer';
            $Comment->parent_id = $request->input('post_id');
            $Comment->name = $request->input('name');
            $Comment->post_id = $CommentDetail->post_id;
            $Comment->type = $CommentDetail->type;
            $Comment->status = 1;
            $Comment->save();
            return response()->json(['msg' => 'کامنت شما با موفقیت ثبت شد.'], 200);

        } catch (\Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }
}
