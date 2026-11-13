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
    // Sidebar: opening a submenu closes the other open ones at the same level, so only one stays open.
    document.addEventListener('click', function (e) {
        var link = e.target.closest('.aside-menu .menu-accordion > .menu-link');
        if (!link) return;
        var item = link.parentElement;
        var root = item.closest('[data-kt-menu="true"]');
        var menu = root && window.KTMenu && KTMenu.getInstance(root);
        Array.prototype.forEach.call(item.parentElement.children, function (sib) {
            if (sib === item || !sib.classList.contains('menu-accordion') || !sib.classList.contains('show')) return;
            var sub = sib.querySelector(':scope > .menu-sub');
            if (!sub) return; // plain links such as the dashboard
            if (menu && typeof menu.hide === 'function') {
                menu.hide(sib);
            } else {
                sib.classList.remove('show', 'hover');
                sub.classList.remove('show');
                sub.style.display = 'none';
            }
        });
    });
</script>
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
