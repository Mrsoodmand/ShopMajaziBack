<?php

namespace App\Http\Controllers\Bot;

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

class HomeController extends Controller
{

    public function index(Request $request)
    {
        return view('admin-bot.users.index');
    }

    public function getBackUp(Request $request)
    {
        if ($request->type == 'member'){
            $members = DB::table('members')->get();
            foreach ($members as $member){

                $checkUser = HegzaUser::where('user_id',$member->userid)->first();
                if (is_null($checkUser)){

                    $newUser = new HegzaUser();
                    $newUser->user_id = $member->userid;
                    $newUser->name = $member->fname;
                    $newUser->username = $member->username;
                    $newUser->path = 'start';
                    $newUser->wallet = $member->score;
                    $newUser->parent = '';
                    $newUser->ref = $member->userid;
                    $newUser->test = $member->use_test;
                    $newUser->from = 'bot';
                    $newUser->message = 0;
                    $newUser->percent = 10;
                    $newUser->save();

                }
            }
        }
    }

}
