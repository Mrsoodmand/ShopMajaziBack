<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Pages;
use App\Models\Setting;
use App\Models\Virtual_Country;
use App\Models\Virtual_Service;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class SettingController extends Controller
{
    public function VirtualNumber()
    {
        $Page = Pages::find(1);
        $Setting = Setting::find(3);
        $Country = Virtual_Country::select('land','pva','man','active')->get();
        $Service = Virtual_Service::select('land','pva','man','active')->get();

        return view('admin-v2.virtual-number.settings', compact('Page', 'Setting','Service','Country'));
    }

    public function SubmitVirtualNumberSetting(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'h1' => 'required',
                'detail' => 'required',
                'meta_title' => 'required',
                'meta_desc' => 'required',
            ], [
                'h1.required' => 'لطفا تگ H1 را وارد نمایید.',
                'detail.required' => 'لطفا توضیحات بلند را وارد نمایید.',
                'meta_title.required' => 'لطفا متا تایتل را وارد نمایید',
                'meta_desc.required' => 'لطفا متا دسکریپشن را وارد نمایید.',
            ]);

        if ($Validate->fails()) {
            return response()->json(['error' => $Validate->errors()->all()], 400);
        }
        try {

            $Page = Pages::find(1);
            // This form has no FAQ or share-image inputs: keep what React admin (settings, SEO) saved there.
            $Seo = json_decode((string)$Page->seo, true);
            $Seo = is_array($Seo) ? $Seo : [];
            foreach (Add_Seo($request) as $Key => $Value) {
                if ($Key !== 'meta_image' || $request->has('SeoImage')) {
                    $Seo[$Key] = $Value;
                }
            }
            $Page->h1 = $request->input('h1');
            $Page->long_detail = $request->input('detail');
            $Page->image = $request->input('image_gallery');
            $Page->seo = json_encode($Seo);
            if ($request->has('question')) {
                $Page->faq = json_encode(Add_Faq($request));
            }
            $Page->save();

            $Status = [
                'pva' => $request->input('pva_status'),
                'man' => $request->input('man_status'),
                'land' => $request->input('land_status'),
                'active' => $request->input('active_status'),
            ];

            $Setting = Setting::find(3);
            $Setting->key = json_encode($Status);
            $Setting->save();

            return response()->json(['success' => 'تنظمیات با موفقیت ثبت شد.']);

        } catch (\Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }


    public function VirtualService()
    {
        $Page = Pages::find(6);
        return view('admin-v2.virtual-service.settings', compact('Page'));
    }

    /*  */
    public function VirtualServiceSubmit(Request $request)
    {
        $Validate = Validator::make($request->all(),
            [
                'h1' => 'required',
                'detail' => 'required',
                'meta_title' => 'required',
                'meta_desc' => 'required',
            ], [
                'h1.required' => 'لطفا تگ H1 را وارد نمایید.',
                'detail.required' => 'لطفا توضیحات بلند را وارد نمایید.',
                'meta_title.required' => 'لطفا متا تایتل را وارد نمایید',
                'meta_desc.required' => 'لطفا متا دسکریپشن را وارد نمایید.',
            ]);

        if ($Validate->fails()) {
            return response()->json(['error' => $Validate->errors()->all()], 400);
        }
        try {

            $Seo = Add_Seo($request);
            $Faq = Add_Faq($request);

            $Page = Pages::find(6);
            $Page->h1 = $request->input('h1');
            $Page->long_detail = $request->input('detail');
            $Page->image = $request->input('image_gallery');
            $Page->seo = json_encode($Seo);
            $Page->faq = json_encode($Faq);
            $Page->save();

            return response()->json(['success' => 'تنظمیات با موفقیت ثبت شد.']);

        } catch (\Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }
}
