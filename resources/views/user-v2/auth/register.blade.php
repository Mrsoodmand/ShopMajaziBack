@extends('site.layout.master',['title' => 'ثبت نام'])

@section('content')

    <div class="user-area ptb-100 mt-5">
        <div class="container">
            <div class="user-area" id="register">
            <div class="user-item">

                <form id="RegisterForm">
                    <h2> ثبت نام </h2>
                  <div class="row">
                      <div class="col-6">
                          <div class="form-group">
                              <input type="tel" name="mobile" class="form-control" placeholder="شماره موبایل">
                              <input type="hidden" name="product_id" class="form-control product_id">
                              <p id="mobile-register" class="Login" style="display: none"></p>
                          </div>
                      </div>
                      <div class="col-6">
                          <div class="form-group">
                              <input type="text" name="ref_code" value="{{isset($_GET['ref-token']) ? $_GET['ref-token'] : ''}}"
                                     {{isset($_GET['ref-token']) ? "disabled" : ''}}
                                     class="form-control" placeholder="کد معرف اختیاری">
                          </div>
                      </div>
                  </div>
                    @csrf
                    <div class="row register-error-area">


                    </div>
                    <button type="button" id="btn-register" class="btn common-btn">
                        ارسال کد
                        <img src="{{secure_url('/site/assets/images/shape1.png')}}" alt="Shape">
                        <img src="{{secure_url('/site/assets/images/shape2.png')}}" alt="Shape">
                    </button>
                </form>
            </div>
        </div>
        </div>
    </div>
@endsection
