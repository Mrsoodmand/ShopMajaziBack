function Register() {
    $("#login").hide(), $("#register").show()
}

function Login() {
    $("#register").hide(), $("#login").show()
}

function BankRadio() {
    $("#wallet-desc").hide(), $("#bank-desc").show()
}

function Wallet() {
    $("#wallet-desc").show(), $("#bank-desc").hide()
}

function HeaderDetail() {
    setInterval(function () {
        var e = $(".active-header").removeClass("active-header").hide().data("next");
        $("#top-header-" + e).addClass("active-header").fadeIn()
    }, 2e3)
}

jQuery(function (e) {
    "use strict";
    if (e(window).on("scroll", function () {
        e(this).scrollTop() > 150 ? e(".main-nav").addClass("menu-shrink") : e(".main-nav").removeClass("menu-shrink")
    }), jQuery(".mean-menu").meanmenu({meanScreenWidth: "991"}), e(".modal a").not(".dropdown-toggle").on("click", function () {
        e(".modal").modal("hide")
    }), jQuery("#rev_slider_1").show().revolution({
        sliderLayout: "auto",
        responsiveLevels: [1240, 1024, 778, 480],
        gridwidth: [1240, 1024, 778, 480],
        gridheight: [700, 700, 1e3, 1300],
        visibilityLevels: [1240, 1024, 1024, 480],
        navigation: {
            arrows: {
                enable: !0,
                style: "gyges",
                hide_onleave: !1,
                left: {container: "layergrid", h_align: "center", v_align: "bottom", h_offset: -30, v_offset: 155},
                right: {container: "layergrid", h_align: "center", v_align: "bottom", h_offset: 30, v_offset: 155}
            }
        }
    }), e(".banner-slider").owlCarousel({
        rtl: !0,
        items: 1,
        loop: !0,
        margin: 15,
        nav: !0,
        dots: !1,
        smartSpeed: 1e3,
        autoplay: !0,
        autoplayTimeout: 4e3,
        autoplayHoverPause: !0,
        navText: ["<i class='bx bx-right-arrow-alt'></i>", "<i class='bx bx-left-arrow-alt'></i>"]
    }), e("#Container").mixItUp(), e(".testimonials-slider").owlCarousel({
        rtl: !0,
        loop: !0,
        center: !0,
        margin: 15,
        nav: !0,
        dots: !1,
        smartSpeed: 1e3,
        autoplay: !0,
        autoplayTimeout: 4e3,
        autoplayHoverPause: !0,
        navText: ["<i class='bx bx-right-arrow-alt'></i>", "<i class='bx bx-left-arrow-alt'></i>"],
        responsive: {0: {items: 1}, 768: {items: 2}, 992: {items: 3}}
    }), e(".testimonials-slider-two").owlCarousel({
        rtl: !0,
        items: 1,
        loop: !0,
        margin: 15,
        nav: !0,
        dots: !1,
        smartSpeed: 1e3,
        autoplay: !0,
        autoplayTimeout: 4e3,
        autoplayHoverPause: !0,
        navText: ["<i class='bx bx-right-arrow-alt'></i>", "<i class='bx bx-left-arrow-alt'></i>"]
    }), e(".minus").on("click", function () {
        var t = e(this).parent().find("input"), s = parseInt(t.val()) - 1;
        return s = s < 1 ? 1 : s, t.val(s), t.change(), !1
    }), e(".plus").on("click", function () {
        var t = e(this).parent().find("input");
        return t.val(parseInt(t.val()) + 1), t.change(), !1
    }), jQuery(window).on("load", function () {
        jQuery(".loader").fadeOut(500)
    }), e(window).on("scroll", function () {
        var t = e(window).scrollTop();
        t > 100 && e(".go-top").addClass("active"), t < 100 && e(".go-top").removeClass("active")
    }), e(".go-top").on("click", function () {
        e("html, body").animate({scrollTop: "0"}, 500)
    }), e(".sorting-slider").owlCarousel({
        rtl: !0,
        loop: !0,
        margin: 15,
        nav: !0,
        dots: !1,
        smartSpeed: 1e3,
        autoplay: !0,
        autoplayTimeout: 4e3,
        autoplayHoverPause: !0,
        navText: ["<i class='bx bx-right-arrow-alt'></i>", "<i class='bx bx-left-arrow-alt'></i>"],
        responsive: {0: {items: 2}, 768: {items: 4}, 992: {items: 6}}
    }), null !== document.getElementById("days")) {
        const e = 1e3, t = 60 * e, s = 60 * t, o = 24 * s;
        let r = new Date("February 25, 2021 00:00:00").getTime();
        setInterval(function () {
            let a = (new Date).getTime(), n = r - a;
            document.getElementById("days").innerText = Math.floor(n / o), document.getElementById("hours").innerText = Math.floor(n % o / s), document.getElementById("minutes").innerText = Math.floor(n % s / t), document.getElementById("seconds").innerText = Math.floor(n % t / e)
        }, e)
    }

    function t() {
        e(".newsletter-form").addClass("animated shake"), setTimeout(function () {
            e(".newsletter-form").removeClass("animated shake")
        }, 1e3)
    }

    function s(t, s) {
        if (t) var o = "validation-success"; else o = "validation-danger";
        e("#validator-newsletter").removeClass().addClass(o).text(s)
    }

    e(".js-modal-btn").modalVideo(), e(".video-slider").owlCarousel({
        rtl: !0,
        loop: !0,
        margin: 15,
        nav: !0,
        dots: !1,
        smartSpeed: 1e3,
        autoplay: !0,
        autoplayTimeout: 4e3,
        autoplayHoverPause: !0,
        navText: ["<i class='bx bx-right-arrow-alt'></i>", "<i class='bx bx-left-arrow-alt'></i>"],
        responsive: {0: {items: 1}, 768: {items: 2}, 992: {items: 2}}
    }), e(".accordion > li:eq(0) .faq-head").addClass("active").next().slideDown(), e(".accordion .faq-head").on("click", function (t) {
        var s = e(this).closest("li").find(".faq-content");
        e(this).closest(".accordion").find(".faq-content").not(s).slideUp(300), e(this).hasClass("active") ? e(this).removeClass("active") : (e(this).closest(".accordion").find(".faq-head.active").removeClass("active"), e(this).addClass("active")), s.stop(!1, !0).slideToggle(300), t.preventDefault()
    }), setTimeout(function () {
        e("#modal-subscribe").modal("show")
    }, 1500), e(".newsletter-form").validator().on("submit", function (e) {
        e.isDefaultPrevented() ? (t(), s(!1, "Please enter your email correctly.")) : e.preventDefault()
    })
}(jQuery)), $("#LoginForm").submit(function () {
    return $("#LoginForm").ajaxSubmit({
        dataType: "json", beforeSend: function () {
            $("#mobile-register").css("display", "none"), $("#password-register").css("display", "none"), $("#btn-register").html("لطفا صبر کنید")
        }, success: function (e) {
            location.reload()
        }, error: function (e) {
            console.log(e), null != e.responseJSON.errors.mobile && $("#mobile-register").css("display", "block").html(e.responseJSON.errors.mobile), null != e.responseJSON.errors.password && $("#password-register").css("display", "block").html(e.responseJSON.errors.password), null != e.responseJSON.errors.password_confirmation && $("#password-register").css("display", "block").html(e.responseJSON.errors.password_confirmation), $("#btn-register").html("ثبت نام")
        }
    }), !1
}),
    $("#btn-register").click(function () {
    var e = $("#RegisterForm").serialize();
    return $.ajax({
        type: "post",
        url: "https://shopmajazi.com/user/register/step1",
        data: e, dataType: "json",
        beforeSend: function () {
            $("#btn-register").html("لطفا صبر کنید ... "), $("#mobile-register-2").css("display", "none"), $("#code-register").css("display", "none"), $("#password-register").css("display", "none")
        }, success: function (e) {
            toastr.success(e.success), $("#register").empty().append(e.data), $("#btn-register-2").click(function () {
                var e = $("#Step2").serialize();
                return $.ajax({
                    type: "post",
                    url: "https://shopmajazi.com/user/register/step2",
                    data: e,
                    dataType: "json",
                    beforeSend: function () {
                        $("#btn-register-2").html("لطفا صبر کنید ... "), $(".register-error-area").empty()
                    },
                    success: function (e) {
                        toastr.success(e.success, "عملیات با موفقیت انجام شد."), location.reload()
                    },
                    error: function (e) {
                        console.log(e), $("#btn-register-2").html("ثبت نام"), $(".alert-success-register").remove(), $(".register-error-area").empty(), e.responseJSON.error.forEach(function (e) {
                            $(".register-error-area").append(' <p id="errors" class="Login"> ' + e + " </p>")
                        })
                    }
                }), !1
            })
        }, error: function (e) {
            console.log(e), $("#btn-register").html("ثبت نام"), $(".register-error-area").empty(), e.responseJSON.error.forEach(function (e) {
                $(".register-error-area").append(' <p id="errors" class="Login"> ' + e + " </p>")
            })
        }
    }), !1
}), toastr.options = {
    closeButton: !0,
    debug: !1,
    newestOnTop: !1,
    progressBar: !0,
    positionClass: "toast-top-center",
    preventDuplicates: !1,
    showDuration: "300",
    hideDuration: "1000",
    timeOut: "5000",
    extendedTimeOut: "1000",
    showEasing: "swing",
    hideEasing: "linear",
    showMethod: "fadeIn",
    hideMethod: "fadeOut"
}, $(document).ready(function () {
    HeaderDetail()
}), $(".SearchMenu").keyup(function () {
    var e = $(this).data("class-target"), t = $(this).val();
    count = 0, $("." + e).each(function () {
        $(this).text().search(new RegExp(t, "i")) < 0 ? $(this).fadeOut() : ($(this).fadeIn(), count++)
    })
});
