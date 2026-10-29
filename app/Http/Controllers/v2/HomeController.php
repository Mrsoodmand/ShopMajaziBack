<?php

namespace App\Http\Controllers\v2;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Mail\Subscribe;
use App\Models\Blog;
use App\Models\Comment;
use App\Models\ContactUs;
use App\Models\Ip;
use App\Models\Product;
use App\Models\Virtual_Country;
use App\Models\Virtual_Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\File;
use SimpleXMLElement;

class HomeController extends Controller
{
    protected $ViewFolder = 'site-v2';

    public function Home(Request $request)
    {
        $Jdf = new Jdf();
        $Blogs = Blog::orderby('id', 'DESC')->select('created_at', 'short_detail', 'title', 'slug', 'image_big')->where('status',2)->where('type', 'blog')->take(12)->get();
        return view($this->ViewFolder . '.home',compact('Blogs','Jdf'));
    }

    public function ContactUs()
    {
        return view('site-v2.contact-us', ['PageSeo' => app(\App\Services\PageSeo::class)->resolved('contact-us')]); // admin › SEO
    }

    public function submitContactUs(Request $request)
    {
        $Validate = Validator::make($request->all(), [
            'name' => 'required|string|min:3|max:50',
            'email' => 'required|email|min:3|max:50',
            'mobile' => 'required|digits:11',
            'subject' => 'required|string|min:3|max:50',
            'message' => 'required|string|min:3|max:300',
        ], [
            'name.required' => 'لطفا نام و نام خانوادگی را وارد نمایید.',
            'name.string' => 'فرمت نام و نام خانوادگی نامعتبر می باشد.',
            'name.min' => 'حداقل 3 کاراکتر برای نام و نام خانوادگی مجاز می باشد.',
            'name.max' => 'حداکثر 50 کاراکتر برای نام و نام خانوادگی مجاز می باشد.',
            'email.required' => 'لطفا آدرس ایمیل را وارد نمایید.',
            'email.email' => 'فرمت آدرس ایمیل نامعتبر می باشد.',
            'email.min' => 'حداقل 3 کاراکتر برای آدرس ایمیل مجاز می باشد.',
            'email.max' => 'حداکثر 50 کاراکتر برای آدرس ایمیل مجاز می باشد.',
            'mobile.required' => 'لطفا شماره موبایل را وارد نمایید.',
            'mobile.digits' => 'شماره موبایل را صحیح وارد نمایید.',
            'subject.required' => 'لطفا عنوان را وارد نمایید.',
            'subject.string' => 'فرمت عنوان نامعتبر می باشد.',
            'subject.min' => 'حداقل 3 کاراکتر برای عنوان مجاز می باشد.',
            'subject.max' => 'حداکثر 50 کاراکتر برای عنوان مجاز می باشد.',
            'message.required' => 'لطفا متن درخواست را وارد نمایید.',
            'message.string' => 'فرمت متن درخواست نامعتبر می باشد.',
            'message.min' => 'حداقل 3 کاراکتر برای متن درخواست مجاز می باشد.',
            'message.max' => 'حداکثر 50 کاراکتر برای متن درخواست مجاز می باشد.',
        ]);
        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {
            $ContactUs = new ContactUs();
            $ContactUs->name = strip_tags($request->input('name'));
            $ContactUs->email = strip_tags($request->input('email'));
            $ContactUs->mobile = strip_tags($request->input('mobile'));
            $ContactUs->subject = strip_tags($request->input('subject'));
            $ContactUs->message = strip_tags($request->input('message'));
            $ContactUs->save();

            $Subject = $ContactUs->subject;
            $UserName = $ContactUs->name;
            $UserMobile = $ContactUs->mobile;
            $Message = "
💬 فرم درخواست با ما جدیدی ثبت شد!
🔴 عنوان فرم: $Subject
👤 نام کاربر: $UserName
📞 شماره کاربر: $UserMobile
";
            SendTelegramMessage($Message, -1001509696194);

            return response()->json(['success' => 'درخواست شما با موفقیت ثبت شد. همکارانمان به زودی با شما تماس خواهند گرفت.'], 200);
        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* ارسال نظر */
    public function sendComment(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'name' => 'required',
                'mobile' => 'required|digits:11',
                'type' => 'required',
                'comment' => 'required|string|max:300|min:30',
                'post_id' => 'required|numeric',
            ],
            [
                'name.required' => 'لطفا نام خود را وارد نمایید',
                'mobile.required' => 'لطفا شماره موبایل خود را وارد نمایید',
                'mobile.digits' => 'فرمت شناره موبایل نا معتبر میباشد',
                'type.required' => 'خطا در ثبت نظر کد1',
                'comment.required' => 'لطفا متن نظر را وارد نمایید',
                'comment.string' => 'فرمت نظر نامعتبر میباشد',
                'comment.max' => 'حداکثر 300 کلمه برای نظر مجاز می باشد',
                'comment.min' => 'حداقل 30 کلمه برای نظر مجاز می باشد',
                'post_id.required' => 'خطا در ثبت نظر کد 2',
            ]);

        if ($Validate->fails()) {
            return validation_error($request, $Validate->errors()->all());
        }
        try {
            if ($request->input('type') == 'product') {
                $Blog = Product::find(Decode($request->input('post_id')));
                if (!is_null($Blog)) {
                    $Comment = new Comment();
                    $Comment->post_id = $Blog->id;
                    $Comment->type = $request->input('type');
                    if (Auth::guard('user')->check()) {
                        $Comment->user_id = Auth::guard('user')->user()->id;
                    }
                    $Comment->name = $request->input('name');
                    $Comment->mobile = $request->input('mobile');
                    $Comment->status = 'Pending';
                    $Comment->text = $request->input('comment');
                    $Comment->save();
                    return success($request, 'با تشکر از شما! نظر شما با موفقیت ثبت گردید پس از تایید در سایت منشتر خواهد شد.');
                } else {
                    return exception_error($request, 'مطلب مورد نظر یافت نشد');
                }
            } elseif ($request->input('type') == 'blog') {

                $Blog = Blog::find($request->input('post_id'));
                if (!is_null($Blog)) {
                    $Comment = new Comment();
                    $Comment->post_id = $Blog->id;
                    $Comment->type = $request->input('type');
                    if (Auth::guard('user')->check()) {
                        $Comment->user_id = Auth::guard('user')->user()->id;
                    }
                    $Comment->name = $request->input('name');
                    $Comment->mobile = $request->input('mobile');
                    $Comment->status = 'Pending';
                    $Comment->text = $request->input('comment');
                    $Comment->save();
                    return success($request, 'با تشکر از شما! نظر شما با موفقیت ثبت گردید پس از تایید در سایت منشتر خواهد شد.');

                } else {
                    return exception_error($request, 'مطلب مورد نظر یافت نشد');
                }
            }
            elseif ($request->input('type') == 'page') {
                $Pages = __('order.PageType');
                $PostId = $request->input('post_id');
                if (array_key_exists($PostId, $Pages)) {
                    $Comment = new Comment();
                    $Comment->post_id = $PostId;
                    $Comment->type = 'page';
                    if (Auth::guard('user')->check()) {
                        $Comment->user_id = Auth::guard('user')->user()->id;
                    } else {
                        $Comment->user_id = 0;
                    }
                    $Comment->name = $request->input('name');
                    $Comment->mobile = $request->input('mobile');
                    $Comment->status = 0;
                    $Comment->text = $request->input('comment');
                    $Comment->save();
                    return success($request, 'با تشکر از شما! نظر شما با موفقیت ثبت گردید پس از تایید در سایت منشتر خواهد شد.');
                } else {
                    return exception_error($request, 'مطلب مورد نظر یافت نشد');
                }
            }elseif ($request->input('type') == 'service') {

                $Blog = Product::find($request->input('post_id'));
                if (!is_null($Blog)) {
                    $Comment = new Comment();
                    $Comment->post_id = $Blog->id;
                    $Comment->type = $request->input('type');
                    if (Auth::guard('user')->check()) {
                        $Comment->user_id = Auth::guard('user')->user()->id;
                    }
                    $Comment->name = $request->input('name');
                    $Comment->mobile = $request->input('mobile');
                    $Comment->status = 'Pending';
                    $Comment->text = $request->input('comment');
                    $Comment->save();
                    return success($request, 'با تشکر از شما! نظر شما با موفقیت ثبت گردید پس از تایید در سایت منشتر خواهد شد.');

                } else {
                    return exception_error($request, 'مطلب مورد نظر یافت نشد');
                }
            }
            return exception_error($request, 'خطا در ثبت نظر لطفا مجدد تلاش کنید');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }
}
