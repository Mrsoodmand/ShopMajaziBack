</div>
<!--**********************************
    Main wrapper end
***********************************-->

<!--**********************************
    Scripts
***********************************-->
<!-- Required vendors -->
<script src="{{URL::asset('admin/vendor/global/global.min.js')}}"></script>
<script src="{{URL::asset('admin/vendor/bootstrap-select/dist/js/bootstrap-select.min.js')}}"></script>
<script src="{{URL::asset('admin/vendor/chart.js/Chart.bundle.min.js')}}"></script>
<script src="{{URL::asset('admin/js/custom.min.js')}}"></script>
<script src="{{URL::asset('admin/js/deznav-init.js')}}"></script>
<script src="{{URL::asset('admin/vendor/owl-carousel/owl.carousel.js')}}"></script>
{{-- "Choose image" buttons (data-ckfinder-field) open the CKFinder media library. --}}
<script src="/js/admin-image-picker.js" data-connector="{{ route('admin.ckfinder_connector', [], false) }}"></script>



@yield('footer')
<script>




    function carouselReview() {
        /*  testimonial one function by = owl.carousel.js */
        /*  testimonial one function by = owl.carousel.js */
        jQuery('.testimonial-one').owlCarousel({
            loop: true,
            margin: 10,
            nav: false,
            center: true,
            dots: false,
            navText: ['<i class="fa fa-caret-left"></i>', '<i class="fa fa-caret-right"></i>'],
            responsive: {
                0: {
                    items: 2
                },
                400: {
                    items: 3
                },
                700: {
                    items: 5
                },
                991: {
                    items: 6
                },

                1200: {
                    items: 4
                },
                1600: {
                    items: 5
                }
            }
        })
    }

    jQuery(window).on('load', function () {
        setTimeout(function () {
            carouselReview();
        }, 1000);
    });
</script>
</body>

</html>
