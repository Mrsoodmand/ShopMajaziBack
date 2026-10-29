<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Virtual_Country;
use App\Models\Virtual_Products;
use App\Models\Virtual_Service;
use App\Services\VirtualNumberOffers;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use SebastianBergmann\Type\Exception;

class VirtualCountryController extends Controller
{
    /* countries list */
    public function CountriesList(Request $request)
    {
        $list = Virtual_Country::orderby('id', 'DESC');
        if ($request->filled('search')) {
            $Search = '%' . trim($request->input('search')) . '%';
            $list->where(fn($q) => $q->where('faName', 'like', $Search)->orWhere('enName', 'like', $Search)->orWhere('code', 'like', $Search));
        }
        $list = $list->paginate(50);
        return view('admin-v2.virtual-number.country.index', compact('list'));
    }

    /* single country */
    public function create()
    {
        $Category = Category::where('type', 'virtual-number-category')->get();
        return view('admin-v2.virtual-number.country.create', compact('Category'));
    }

    /* ثبت کشور */
    public function StoreCreate(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'slug' => 'required|string',
//                'code' => 'required|string',
//                'special' => 'required',
//                'sort' => 'required',

                'meta_title' => 'required',
                'meta_desc' => 'required',
//                'detail' => 'required',
            ]);
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Service = new Virtual_Country();
            $Service->faName = $request->input('faName');
            $Service->enName = $request->input('enName');
            $Service->category_id = $request->input('category_id');
            $Service->image = $request->input('image_gallery');
            $Service->code = $request->input('pva');
            $Service->man = $request->input('man');
            $Service->active = $request->input('active');
            $Service->pva = $request->input('pva');
            $Service->land = $request->input('land');
            $Service->slug = $request->input('slug');
            $Service->status = $request->input('status');
            $Service->meta_title = $request->input('meta_title');
            $Service->meta_desc = $request->input('meta_desc');
            $Service->seo = $request->input('seo');
            $Service->long_detail = $request->input('detail');
            $Service->special = $request->input('special');
            $Service->sort = $request->input('sort');
            $Service->save();

            return success($request, 'با موفقیت ویرایش شد');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    /* single country */
    public function Single($id)
    {
        $Category = Category::where('type', 'virtual-number-category')->get();
        $edit = Virtual_Country::find($id);
        return view('admin-v2.virtual-number.country.create', compact('edit', 'Category'));
    }

    /* ویرایش کشور */
    public function Update(Request $request)
    {
        $validate = Validator::make($request->all(),
            [
                'faName' => 'required|string',
                'enName' => 'required|string',
                'slug' => 'required|string',
//                'code' => 'required|string',
//                'special' => 'required',
                'sort' => 'required',

                'meta_title' => 'required',
                'meta_desc' => 'required',
//                'detail' => 'required',
            ]);
        if ($validate->fails()) {
            return validation_error($request, $validate->errors()->all());
        }
        try {

            $Service = Virtual_Country::find($request->input('id'));
            $Service->faName = $request->input('faName');
            $Service->enName = $request->input('enName');
            $Service->category_id = $request->input('category_id');
            $Service->image = $request->input('image_gallery');
            // `code` is the flag (image/svg/{code}.svg); the PVA code only fills it when it is missing.
            $Service->code = $Service->code ?: $request->input('pva');
            $Service->man = $request->input('man');
            $Service->active = $request->input('active');
            $Service->pva = $request->input('pva');
            $Service->land = $request->input('land');
            $Service->slug = $request->input('slug');
            $Service->meta_title = $request->input('meta_title');
            $Service->meta_desc = $request->input('meta_desc');
            $Service->status = $request->input('status');
            $Service->seo = $request->input('seo');
            $Service->long_detail = $request->input('detail');
            if ($request->has('special')) { // not on the form; don't wipe it
                $Service->special = $request->input('special');
            }
            $Service->sort = $request->input('sort');
            $Service->save();

            return success($request, 'با موفقیت ویرایش شد');

        } catch (\Exception $exception) {
            return exception_error($request, $exception->getMessage());
        }
    }

    public function FastUpdate(Request $request)
    {
        try {
            $Service = Virtual_Country::find($request->input('id'));
            $Service->land = trim($request->input('land'));
            $Service->man = trim($request->input('man'));
            $Service->pva = trim($request->input('pva'));
            $Service->active = trim($request->input('active'));
            $Service->save();

            return success($request, 'با موفقیت ویرایش شد');
        } catch (Exception $exception) {
            return exception_error($request, $exception->getMessage());

        }
    }

    /* «بروز رسانی کشور»: builds this country's offers for every service (App\Services\VirtualNumberOffers). */
    public function GenerateProduct($id)
    {
        $Country = Virtual_Country::findOrFail($id);
        $Result = app(VirtualNumberOffers::class)->rebuildCountry($Country);

        return redirect()->route('admin.Single-Virtual-Countries', $Country->id)
            ->with('success', "پیشنهادها بازسازی شد: {$Result['created']} ردیف تازه، {$Result['updated']} ردیف به‌روز.");
    }
}
