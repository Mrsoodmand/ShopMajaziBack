@if(\Illuminate\Support\Facades\Session::has('error'))
    @foreach(\Illuminate\Support\Facades\Session::get('error') as $key => $error)
        <div class="d-flex align-items-center bg-danger rounded p-1 mb-7">
                            <span class="svg-icon svg-icon-danger text-white me-3" style="margin-right: 10px"><span
                                    class="svg-icon svg-icon-1 text-white"><i
                                        class="fa-regular fa-circle-exclamation text-white"></i></span></span>
            <div class="flex-grow-1 me-2">
                <p class="fw-bolder mt-3 text-white text-hover-primary fs-6">{{$error}}</p>
            </div>
        </div>
    @endforeach
@endif
@if(\Illuminate\Support\Facades\Session::has('success'))
    <div class="d-flex align-items-center bg-success rounded p-1 mb-7">
                            <span class="svg-icon svg-icon-danger text-white me-3" style="margin-right: 10px"><span
                                    class="svg-icon svg-icon-1 text-white"><i class="fa-regular fa-badge-check text-white"></i></span></span>
        <div class="flex-grow-1 me-2">
            <p class="fw-bolder mt-3 text-white text-hover-primary fs-6">{{\Illuminate\Support\Facades\Session::get('success')}}</p>
        </div>
    </div>
@endif
