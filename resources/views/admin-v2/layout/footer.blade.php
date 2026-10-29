</div>
<!--end::Wrapper-->
</div>
<!--end::Page-->
</div>

<script>var hostUrl = "assets/";</script>
<script src="{{secure_url('/')}}/site/assets/js/jquery.min.js"></script>
<script src="{{secure_url('admin-v2/assets/plugins/global/plugins.bundle.js')}}"></script>
<script src="{{secure_url('admin-v2/assets/js/scripts.bundle.js')}}"></script>
{{-- "Add image" buttons (data-ckfinder-field) open the CKFinder media library. --}}
<script src="/js/admin-image-picker.js" data-connector="{{ route('admin.ckfinder_connector', [], false) }}"></script>
@yield('footer')
<script>
    $(document).on('click', '.check-payment', function () {
        var id = $(this).data('payment');
        $.ajax({
            url: '{{route('admin.Single-Payment')}}',
            dataType: 'JSON',
            method: "GET",
            data: {
                id: id
            }, success: function (data) {
                $('#PaymentDetail').empty().append(data.data);
                $('#PaymentModal').modal('toggle');
            }
        });
    });
</script>
<!--end::Page custom Javascript-->
<!--end::Javascript-->
</body>
<!--end::Body-->
</html>
