
<form id="SubmitForm" class="form" method="post" action="{{route('admin.submit-comment-answer')}}" enctype="multipart/form-data">
    <div class="mb-13 text-center">
        <h1 class="mb-3">جزئیات کامنت</h1>
        <div class="text-muted fw-bold fs-5">جزئیات کامنت کاربر</div>
    </div>
    <div class="row g-9 mb-8">
        <div class="col-md-12 fv-row fv-plugins-icon-container">
            <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                <span class="required">نام کاربر</span>
            </label>
            <input type="text" class="form-control form-control-solid" name="name" value="{{$single->name}}">
        </div>
        <div class="col-md-12 fv-row fv-plugins-icon-container">
            <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                <span class="required">متن</span>
            </label>
            <textarea name="" disabled class="form-control form-control-solid" id="" rows="10" cols="30">{{$single->text}}</textarea>
        </div>
    </div>
    <div class="row g-9 mb-8">
        <div class="col-md-12 fv-row fv-plugins-icon-container">
            <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                <span class="required">پاسخ</span>
            </label>
            <textarea name="comment" class="form-control form-control-solid" id="" cols="30" >{{isset($Answer) ? $Answer->text : ''}}</textarea>
            <input type="hidden" value="{{isset($Answer) ? $Answer->id : 0}}" name="id">
            <input type="hidden" value="{{isset($single) ? $single->id : 0}}" name="post_id">
        </div>
    </div>
    @csrf
    <div class="text-center">
        <button type="reset" id="kt_modal_new_target_cancel" class="btn btn-light me-3">انصراف
        </button>
        <button type="submit" data-form="SubmitForm" id="answer-btn" data-text="ثبت پاسخ" class="submit-btn btn btn-primary">
            <span class="indicator-label">ثبت پاسخ</span>
            <span class="indicator-progress">لطفا صبر کنید...
									<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
        </button>
    </div>
</form>
