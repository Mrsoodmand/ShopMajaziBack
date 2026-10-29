<div class="modal-header pb-0 border-0 justify-content-end">
    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"></rect>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"></rect>
								</svg>
							</span>
    </div>
</div>
<div class="modal-body pt-0 pb-15">
    <form action="{{route('admin.bot-message-store')}}" method="post" id="CurrentForm" enctype="multipart/form-data">
        <div class="mb-13 text-center">
            <h1 class="mb-3">{{isset($edit) ? 'ویرایش پیام' : 'ثبت پیام'}}</h1>
        </div>
        <div class="row g-9 mb-8">
            <div class="col-md-12 fv-row fv-plugins-icon-container">
                <div class="d-flex flex-column fv-row">
                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="required">عنوان پیام</span>
                    </label>
                    <input type="text" value="{{isset($edit) ? $edit->title : '' }}"
                           class="form-control form-control-solid"
                           placeholder="عنوان" name="title">
                    <input type="hidden" value="{{isset($edit) ? $edit->id : 0 }}" name="id">
                </div>
            </div>
            <div class="col-md-12 fv-row fv-plugins-icon-container">
                <div class="d-flex flex-column fv-row">
                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="required">متن پیام</span>
                    </label>
                    <textarea name="long_detail" id="" cols="30" rows="10"
                              class="form-control">{!! isset($edit) ? $edit->long_detail : '' !!}</textarea>
                </div>
            </div>
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <div class="d-flex flex-column fv-row">
                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="required">وضعیت</span>
                    </label>
                    <select name="status" id="" class="form-select">
                        <option {{isset($edit) && $edit->status == 1 ? 'selected'  : '' }} value="1">منتشر شده</option>
                        <option {{isset($edit) && $edit->status == 0 ? 'selected'  : '' }} value="0">درانتظار ارسال
                        </option>
                        <option {{isset($edit) && $edit->status == -1 ? 'selected'  : '' }} value="-1">لغو شده</option>
                    </select>
                </div>
            </div>
            <div class="col-md-6 fv-row fv-plugins-icon-container">
                <div class="d-flex flex-column fv-row">
                    <label class="d-flex align-items-center fs-6 fw-semibold mb-2">
                        <span class="required">نوع</span>
                    </label>
                    <select name="type" id="" class="form-select">
                        <option {{isset($edit) && $edit->short_detail == 'all' ? 'selected'  : '' }} value="all">ارسال
                            به
                            همه
                        </option>
                    </select>
                </div>
            </div>
        </div>
        @csrf
        <div class="text-center">
            <button type="button" data-bs-dismiss="modal" class="btn btn-light me-3">انصراف</button>
            <button type="submit" id="submit_btn" class="btn btn-primary submit_btn">
                <span class="indicator-label">ثبت</span>
            </button>
        </div>
    </form>
</div>
