@extends('admin-v2.layout.master')
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-5 mb-xl-8">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">دسته بندی ها</span>
                        </h3>
                        <form action="">
                            <div class="card-toolbar">
                                <div class="d-flex align-items-center position-relative my-1 ms-2">
                                <span class="svg-icon svg-icon-1 position-absolute ms-4">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                         viewBox="0 0 24 24" fill="none">
														<rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546"
                                                              height="2" rx="1" transform="rotate(45 17.0365 15.1223)"
                                                              fill="currentColor"></rect>
														<path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                              fill="currentColor"></path>
													</svg>
												</span>
                                    <input type="text" name="search" data-kt-ecommerce-product-filter="search"
                                           value="{{isset($_GET) && array_key_exists('search',$_GET) ? $_GET['search'] : ''}}"
                                           class="form-control form-control-solid w-250px ps-14"
                                           placeholder="جستجو دسته بندی">
                                </div>
                                <button type="submit" class="btn btn-sm btn-primary ms-2">جستجو</button>
                                <a href="{{route('admin.create category',$type)}}" class="btn btn-sm btn-primary ms-2">افزودن</a>

                            </div>
                        </form>
                    </div>
                    <div class="card-body py-3">
                        <div class="table-responsive">
                            @php $jdf = new \App\lib\Jdf(); @endphp
                            <table class="table table-row-dashed table-row-gray-300 align-middle gs-0 gy-4 mt-3">
                                <thead>
                                <tr class="fw-bolder text-muted">
                                    <th class="ps-4">نام فارسی</th>
                                    <th class="ps-4">نام انگلیسی</th>
                                    <th class="min-w-75px">تاریخ</th>
                                    <th class="text-center">عملیات</th>
                                </tr>
                                </thead>
                                <tbody>
                                @if(count($list) != 0)
                                    @foreach($list as $item)
                                        <tr>
                                            <td class="ps-4">
                                                {{$item->name}}
                                            </td>
                                            <td class="ps-4">
                                                {{$item->enName}}
                                            </td>
                                            <td>
                                                <label class="text-dark fw-bolder text-hover-primary d-block mb-1 fs-6">{{$jdf->jdate('d F Y',strtotime($item->created_at))}}</label>
                                                <span class="text-muted fw-bold d-block fs-7">{{$jdf->jdate('H:i:s',strtotime($item->created_at))}}</span>
                                            </td>
                                            <td>
                                                <div class="d-flex justify-content-center flex-shrink-0">
                                                    <a class="btn btn-bg-light btn-active-color-info btn-sm me-1"
                                                       href="{{route('admin.edit category',$item->id)}}"
                                                       title="اطلاعات">جزئیات</a>
                                                    <button onclick="changeId({{$item->id}})" class="btn text-white btn-bg-danger text-hover-dark btn-sm me-1" type="button" data-bs-toggle="modal" data-bs-target="#kt_modal_new_target">
                                                       حذف
                                                    </button>
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                @else
                                    <tr>
                                        <td colspan="4" class="text-center"> دسته بندی یافت نشد.</td>
                                    </tr>
                                @endif
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="mt-5">
                    {!! $list->links('admin-v2.layout.paginate') !!}
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="kt_modal_new_target" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered mw-650px">
            <div class="modal-content rounded">
                <div class="modal-header pb-0 border-0 justify-content-end">
                    <div class="btn btn-sm btn-icon btn-active-color-primary" data-bs-dismiss="modal">
                    <span class="svg-icon svg-icon-1">
								<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"
                                     fill="none">
									<rect opacity="0.5" x="6" y="17.3137" width="16" height="2" rx="1"
                                          transform="rotate(-45 6 17.3137)" fill="currentColor"/>
									<rect x="7.41422" y="6" width="16" height="2" rx="1"
                                          transform="rotate(45 7.41422 6)" fill="currentColor"/>
								</svg>
							</span>
                    </div>
                </div>
                <div class="modal-body scroll-y px-10 px-lg-15 pt-0 pb-15">
                    <form id="CurrentForm" class="form" action="{{route('admin.delete category')}}"
                          method="post">
                        @csrf
                        <div class="mb-13 text-center">
                            <h1 class="mb-3">حذف دسته بندی</h1>
                        </div>
                        <div class="d-flex flex-column mb-8 fv-row">
                            <!--begin::Tags-->
                            <label class="d-flex align-items-center fs-6 fw-bold mb-2">
                                <span class="required">آیا از حذف این دسته بندی مطمئن هستید؟</span>
                            </label>
                            <input type="hidden" name="id" id="id" value="">
                        </div>
                        <div class="text-center">
                            <button type="reset" id="kt_modal_new_target_cancel"
                                    class="btn btn-light me-3">انصراف
                            </button>
                            <button type="submit" id="submit_btn" class="btn btn-danger">
                                <span class="indicator-label">حذف</span>
                                <span class="indicator-progress">لطفا صبر کنید...
									<span class="spinner-border spinner-border-sm align-middle ms-2"></span>
                            </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

@endsection

@section('footer')
    @include('admin.layout.submit-form')
    <script>
        function changeId(id){
            $('#id').val(id);
        }
    </script>
@endsection

