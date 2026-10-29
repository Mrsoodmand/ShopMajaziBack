<div class="modal-header pb-0 border-0 justify-content-end">
    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal"><span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none"><rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                                       transform="rotate(-45 6 17.3137)" fill="currentColor"/><rect
                                            x="7.41422" y="6" width="16" height="2" rx="1"
                                            transform="rotate(45 7.41422 6)" fill="currentColor"/></svg></span></div>
</div>
<div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
    <form id="CurrentForm" class="form"
          action="{{isset($edit) ? route('admin.update permission') : route('admin.store permission')}}" method="post">
        @csrf
        <div class="mb-13 text-center">
            <h1 class="mb-3">{{isset($edit) ? "ویرایش دسترسی" : 'ایجاد دسترسی'}}</h1>
        </div>
        <div class="d-flex flex-column mb-8">
            <div class="row">
                <div class="col-6">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span class="required">نام دسترسی به فارسی</span>
                    </label>
                    <input type="text" class="form-control form-control-solid"
                           value="{{isset($edit) ? $edit->faName : ''}}" name="faName"/>
                </div>
                <div class="col-6">
                    <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                        <span class="required">نام دسترسی به انگلیسی</span>
                    </label>
                    <input type="text" class="form-control form-control-solid ChangeName"
                           value="{{isset($edit) ? $edit->name : ''}}" name="name"/>
                </div>
                @if(isset($edit))
                    <input type="hidden" class="form-control form-control-solid"
                           value="{{isset($edit) ? $edit->id : ''}}" name="id"/>
                @endif
                <div class="col-6">
                    @php
                        $Table = __('order.type');
                    @endphp
                    <label class="required fs-6 fw-bold mb-2">جدول</label>
                    <select class="form-select form-select-solid" name="table" id="sel1" data-control="select2"
                            data-hide-search="true">
                        <option value="0">همه</option>
                        @foreach($Table as $key => $item)
                            <option value="{{$key}}" {{isset($edit) && $edit->table == $key ? 'selected' : ''}} >{{$item}}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-6">
                    <label class="required fs-6 fw-bold mb-2">دسته بندی</label>
                    <select class="form-select form-select-solid" name="category_id" id="sel1" data-control="select2"
                            data-hide-search="true">
                        @foreach($Categories as $Category)
                            <option value="{{$Category->id}}" {{isset($edit) && $edit->category_id == $Category->id ? 'selected' : ''}} >
                                {{$Category->name}}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>
        <div class="text-center">
            <button type="reset" id="kt_modal_new_target_cancel"
                    class="btn btn-light me-3">انصراف
            </button>
            <button type="submit" id="submit_btn" class="btn btn-primary submit_btn">
                <span class="indicator-label">ثبت</span>
                <span class="indicator-progress">لطفا صبر کنید...
									<span class="spinner-border spinner-border-sm align-middle ms-2"></span></span>
            </button>
        </div>
    </form>
</div>
@include('admin.layout.submit-form')
