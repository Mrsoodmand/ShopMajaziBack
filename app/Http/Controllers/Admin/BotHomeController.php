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

class BotHomeController extends Controller
{

    public function index(Request $request)
    {
        return view('admin-bot.index');
    }
}