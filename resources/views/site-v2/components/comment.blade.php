<section class="container mb-5">
    @php $jdf = new \App\lib\Jdf(); @endphp
    <hr class="mt-4 mb-3">
    <div class="row pt-4 bg-secondary rounded-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center border-bottom pb-4 mb-4">
            <h2 class="h3 mb-0 pt-2">نظرات کاربران</h2>
        </div>
        <div class="col-md-7">
            <div style="position: sticky;top: 4rem">
                @if(count($Comment->where('parent_id',0)) != 0)
                    @foreach($Comment->where('parent_id',0) as $comment)
                        <div class="product-review pb-4 mb-4 border-bottom">
                            <div class="d-flex align-items-start py-4">
                                <div class="ps-3">
                                    <div class="d-flex justify-content-between align-items-center mb-2">
                                        <p class="fs-md mb-0">{{$comment->name}}</p><label class="nav-link-style fs-sm fw-medium"><span class="fs-ms text-muted" style="left: 0px;position: absolute;margin-top: -13px;"><i class="fa-regular fa-clock me-2"></i>{{$jdf->jdate('d F Y',strtotime($comment->created_at))}}</span></label>
                                    </div>
                                    <p class="fs-md mb-1 mt-3">{{$comment->text}}</p>
                                    @php $Reply = $Comment->where('parent_id',$comment->id)->first(); @endphp
                                    @if(!is_null($Reply))
                                        <div class="d-flex align-items-start pt-2 mt-2 ms-4">
                                            <div class="ps-3">
                                                <div class="d-flex justify-content-between align-items-center mb-2">
                                                    <p class="fs-md mb-0">پاسخ مدیر</p><label class="nav-link-style fs-sm fw-medium"><span class="fs-ms text-muted" style="left: 0px;position: absolute;margin-top: -13px;"><i class="fa-regular fa-clock me-2"></i>{{$jdf->jdate('d F Y',strtotime($Reply->created_at))}}</span></label>
                                                </div>
                                                <p class="fs-md mb-1">{{$Reply->text}}</p>
                                            </div>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>
                    @endforeach
                @else
                    <div class="product-review pb-4 mb-4 border-bottom">
                        <div class="d-flex align-items-start py-4">
                            <div class="ps-3">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h6 class="fs-md mb-0">نظر خود را برای ما ارسال کنید</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>
        <div class="col-md-5 mt-2 pt-4 mt-md-0 pt-md-0">
            <div class="bg-secondary py-grid-gutter px-grid-gutter rounded-3" style="position: sticky;top: 4rem">
                <h3 class="h4 pb-2">نظر خود را برای ما ارسال کنید</h3>
                <form class="needs-validation" method="post" action="{{route('sendComment')}}" id="CommentForm"
                      novalidate="">
                    <div class="row">
                        <div class="col-lg-6 col-12">
                            <div class="mb-3">
                                <label class="form-label" for="review-name">نام شما<span
                                            class="text-danger">*</span></label>
                                <input class="form-control" type="text" name="name" @if(\Illuminate\Support\Facades\Auth::guard('user')->check()) value="{{\Illuminate\Support\Facades\Auth::guard('user')->user()->name}}" readonly @endif required="" id="review-name">
                                <div class="invalid-feedback">نام را وارد کنید</div>
                                <small class="form-text text-muted">در نظر نمایش داده می شود</small>
                            </div>
                        </div>
                        <div class="col-lg-6 col-12">
                            <div class="mb-3">
                                <label class="form-label" for="review-email">موبایل شما<span
                                            class="text-danger">*</span></label>
                                <input class="form-control" type="tel" required="" @if(\Illuminate\Support\Facades\Auth::guard('user')->check()) value="{{\Illuminate\Support\Facades\Auth::guard('user')->user()->mobile}}" readonly @endif name="mobile" id="review-email">
                                <div class="invalid-feedback">شماره موبایل را وارد کنید</div>
                                <small class="form-text text-muted">در نظر نمایش داده نمی شود</small>
                            </div>
                        </div>
                    </div>
                    @csrf
                    <div class="mb-3">
                        <label class="form-label" for="review-text">نظر<span class="text-danger">*</span></label>
                        <textarea name="comment" class="form-control" rows="6" required=""
                                  id="review-text"></textarea>
                        <div class="invalid-feedback"> یک بررسی بنویسید!</div>
                        <small class="form-text text-muted">نظر شما باید حداقل 30 نویسه باشد.</small>
                        <input type="hidden" name="type" value="{{$Type}}">
                        <input type="hidden" name="post_id" value="{{$Id}}">
                    </div>
                    <button id="CommentBtn" data-text="ثبت نظر" data-form="CommentForm"
                            class="submit-comment btn btn-primary btn-shadow d-block w-100" type="submit">ثبت نظر
                    </button>
                </form>
            </div>
        </div>
    </div>
</section>
