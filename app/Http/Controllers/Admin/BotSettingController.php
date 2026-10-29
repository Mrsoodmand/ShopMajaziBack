<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\lib\Jdf;
use App\Models\HegzaPayment;
use App\Models\HegzaUser;
use App\Models\Order;
use App\Models\OrderChat;
use App\Models\OrderItem;
use App\Models\HegzaOrder;
use App\Models\Payment;
use App\Models\Wallet;
use App\Models\AppleId;
use App\Models\History;
use App\Models\HegzaIp;
use App\Models\HegzaSetting;
use App\lib\PhpEncrypt;
use App\User;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Mockery\Exception;

class BotSettingController extends Controller
{

    public function index(Request $request)
    {
        $list = HegzaSetting::orderby('id', 'DESC')->get();
        return view('admin-v2.hegza.setting.index', compact('list'));
    }


    public function edit(Request $request, $id)
    {
        $edit = HegzaSetting::find($id);
        return view("admin-v2.hegza.setting.$edit->type", compact('edit'));
    }


    public function update(Request $request)
    {
        $update = $request->input('id');
        $update = HegzaSetting::find($update);
        if (!is_null($update)) {
            switch ($update->type) {
                case 'bank-gate':
                    return $this->bankGate($request);
                    break;
                case 'crypto-gate':
                    return $this->cryptoGate($request);
                    break;
                case 'support':
                    return $this->support($request);
                    break;
                case 'rules':
                    return $this->rules($request);
                    break;
                case 'commission':
                    return $this->commission($request);
                    break;
                case 'menu':
                    return $this->menu($request);
                    break;
                case 'card':
                    return $this->cardGate($request);
                    break;
                case 'setting':
                    return $this->setting($request);
                    break;

            }
        }
    }


    public function bankGate($request)
    {
        $validate = Validator::make($request->all(), [
            'zarinpal' => 'required',
        ],
            ['zarinpal.required' => 'لطفا کد درگاه را وارد کنید']);
        if ($validate->fails()) {
            return response()->json(['error' => $validate->errors()->all()], 400);
        }
        try {
            $Data = [
                'zarinpal' => $request->input('zarinpal')
            ];
            $update = HegzaSetting::find($request->input('id'));
            $update->value = json_encode($Data);
            $update->status = $request->input('status');
            $update->save();

            return response()->json(['msg' => 'کد درگاه با موفقیت ویرایش شد'], 200);

        } catch (Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }


    public function cryptoGate($request)
    {
        try {
            $Data = null;
            foreach ($request->input('cryptoname') as $key => $item) {
                if (!is_null($item)) {
                    $network = $request->input('network')[$key];
                    if (!is_null($network)) {
                        $address = $request->input('address')[$key];
                        if (!is_null($address)) {
                            $Data[] = [
                                'name' => $item,
                                'network' => $network,
                                'address' => $address
                            ];
                        }
                    }
                }
            }
            $update = HegzaSetting::find($request->input('id'));
            $update->value = json_encode($Data);
            $update->status = $request->input('status');
            $update->save();

            return response()->json(['msg' => 'کد درگاه با موفقیت ویرایش شد'], 200);

        } catch (Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }

    }


    public function cardGate($request)
    {
        try {
            $Data = [
                'card' => $request->input('card')
            ];
            $update = HegzaSetting::find($request->input('id'));
            $update->value = json_encode($Data);
            $update->status = $request->input('status');
            $update->save();

            return response()->json(['msg' => 'کد درگاه با موفقیت ویرایش شد'], 200);

        } catch (Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }

    }


    public function support($request)
    {
        try {
            $Data = [
                'support' => $request->input('support')
            ];
            $update = HegzaSetting::find($request->input('id'));
            $update->value = json_encode($Data);
            $update->status = $request->input('status');
            $update->save();

            return response()->json(['msg' => 'کد درگاه با موفقیت ویرایش شد'], 200);

        } catch (Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }

    }


    public function rules($request)
    {
        try {
            $Data = null;
            if ($request->has('rules')) {
                foreach ($request->input('rules') as $key => $item) {
                    if (!is_null($item)) {
                        $Data[] = $item;
                    }
                }
            }

            $update = HegzaSetting::find($request->input('id'));
            $update->value = json_encode($Data);
            $update->status = $request->input('status');
            $update->save();

            return response()->json(['msg' => 'قوانین با موفقیت ثبت شد.'], 200);

        } catch (Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }

    }


    public function menu($request)
    {
        try {
            $Data = [
                'menu' => $request->input('menu')
            ];
            $update = HegzaSetting::find($request->input('id'));
            $update->value = json_encode($Data);
            $update->status = $request->input('status');
            $update->save();

            return response()->json(['msg' => 'متن منو بروز رسانی شد'], 200);

        } catch (Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }


    public function commission($request)
    {
        try {
            $Data = [
                'text' => $request->input('text'),
                'percent' => $request->input('percent'),
                'buy' => $request->input('buy'),
                'renew' => $request->input('renew'),
                'bandwidth' => $request->input('bandwidth'),
            ];
            $update = HegzaSetting::find($request->input('id'));
            $update->value = json_encode($Data);
            $update->status = $request->input('status');
            $update->save();

            return response()->json(['msg' => 'متن پورسانت بروز رسانی شد'], 200);

        } catch (Exception $exception) {
            return response()->json(['error' => [$exception->getMessage()]], 400);
        }
    }


    public function setting($request)
    {
        $Data = [
            'transaction_channel' => $request->input('transaction_channel'),
            'admin_channel' => $request->input('admin_channel'),
        ];
        $update = HegzaSetting::find($request->input('id'));
        $update->value = json_encode($Data);
        $update->status = $request->input('status');
        $update->save();

        return response()->json(['msg' => 'متن منو بروز رسانی شد'], 200);
    }
}
