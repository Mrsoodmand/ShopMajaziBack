@extends('admin-v2.layout.master',['MetaTitle' => 'لیست سِمت ها'])
@section('content')
    <div class="content d-flex flex-column flex-column-fluid" id="kt_content">
        <div class="post d-flex flex-column-fluid" id="kt_post">
            <div id="kt_content_container" class="container-xxl">
                <div class="card mb-5 mb-xl-8">
                    <!--begin::Header-->
                    <div class="card-header border-0 pt-5">
                        <h3 class="card-title align-items-start flex-column">
                            <span class="card-label fw-bolder fs-3 mb-1">لیست دسترسی ها</span>
                        </h3>
                        <div class="card-toolbar my-1">
                            <div class="d-flex align-items-center position-relative my-1 me-6">
                                <!--begin::Svg Icon | path: icons/duotune/general/gen021.svg-->
                                <span class="svg-icon svg-icon-3 position-absolute ms-3">
													<svg xmlns="http://www.w3.org/2000/svg" width="24" height="24"
                                                         viewBox="0 0 24 24" fill="none">
														<rect opacity="0.5" x="17.0365" y="15.1223" width="8.15546"
                                                              height="2" rx="1" transform="rotate(45 17.0365 15.1223)"
                                                              fill="currentColor"/>
														<path d="M11 19C6.55556 19 3 15.4444 3 11C3 6.55556 6.55556 3 11 3C15.4444 3 19 6.55556 19 11C19 15.4444 15.4444 19 11 19ZM11 5C7.53333 5 5 7.53333 5 11C5 14.4667 7.53333 17 11 17C14.4667 17 17 14.4667 17 11C17 7.53333 14.4667 5 11 5Z"
                                                              fill="currentColor"/>
													</svg>
												</span>
                                <!--end::Svg Icon-->
                                <input type="text" id="kt_filter_search"
                                       class="form-control form-control-solid form-select-sm w-150px ps-9"
                                       placeholder="جستجو "/>
                            </div>
                            <button type="button" id="btn-0" class="btn btn-sm btn-primary ms-2 open-modal" data-type="create"
                                    data-id="0">
                                ایجاد سطح دسترسی
                            </button>
                            <a href="{{route('admin.category list',['type' => 'permission'])}}"
                               class="btn btn-sm btn-primary ms-2">ایجاد دسته بندی</a>
                        </div>
                    </div>
                    <div class="card-body py-3">
                        @include('admin-v2.comp.permissions')
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
            <div class="modal-content rounded" id="ModalDetail">
            </div>
        </div>
    </div>

@endsection

@section('footer')
    @include('admin.layout.submit-form')

    <script>
        $(document).on('keyup','.ChangeName',function (){
            var text = $(this).val().replace(/ /g, "-")
            $(this).val(text);
        })

        $(document).on('click', '.open-modal', function () {
            var type = $(this).data('type');
            var id = $(this).data('id');
            var btnId = $(this).attr('id');
            var text = $(this).text();

            if (type == 'create') {
                var url = '{{route('admin.create permission')}}';
            } else {
                var url = '{{route('admin.edit permission')}}';
            }
            $.ajax({
                url: url,
                dataType: 'JSON',
                method: "GET",
                data: {
                    id: id
                },
                beforeSend: function () {
                    $('#'+btnId).empty().append('<span class="spinner-border spinner-border-sm align-middle"></span>');
                    $('#'+btnId).prop('disabled',true);
                },
                success: function (data) {
                    $('#ModalDetail').empty().append(data.data);
                    $('#kt_modal_new_target').modal('toggle');
                    $('#'+btnId).empty().text(text);
                    $('#'+btnId).prop('disabled',false);
                }
            });
        });

    </script>
@endsection

