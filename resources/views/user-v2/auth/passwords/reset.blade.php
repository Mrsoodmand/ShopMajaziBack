@extends('site.layout.master',['title' => 'بازیابی کلمه عبور'])

@section('content')

    <div class="user-area ptb-100 mt-5">
        <div class="container">
            <div class="user-item">
                <form class="form-horizontal" role="form" method="POST" action="{{ url('/user/send-code')}}" id="SendCode">
                    {{ csrf_field() }}
                    <h2>بازیابی کلمه عبور</h2>
                    <div class="row">
                        <div class="col-lg-12 col-12">
                            <div class="form-group">
                                <input type="tel" name="mobile" class="form-control" placeholder="شماره موبایل">
                                @if ($errors->has('mobile'))
                                    <span class="help-block">
                                        <strong>{{ $errors->first('mobile') }}</strong>
                                    </span>
                                @endif
                            </div>
                        </div>

                    </div>
                    <button type="submit" class="btn common-btn">
                        ورود
                        <img src="{{URL::asset('site/assets/images/shape1.png')}}" alt="Shape">
                        <img src="{{URL::asset('site/assets/images/shape2.png')}}" alt="Shape">
                    </button>
                    <h5>حساب کاربری ندارید؟ <a href="{{ url('/user/register') }}">ثبت نام</a></h5>
                </form>
            </div>
        </div>
    </div>
@endsection
