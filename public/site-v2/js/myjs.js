$(document).on('click', '.slow-scroll', function () {
    var target = $(this).data('target');
    $([document.documentElement, document.body]).animate({scrollTop: $("#" + target).offset().top - 250}, 1000);
})

function error(error) {
    toastr.error(error, "خطا", {
        positionClass: "toast-top-right",
        timeOut: 5e3,
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !0,
        preventDuplicates: !0,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
        tapToDismiss: !1,
    });
}

function success(error) {
    toastr.success(error, "", {
        positionClass: "toast-top-right",
        timeOut: 5e3,
        closeButton: !0,
        debug: !1,
        newestOnTop: !0,
        progressBar: !0,
        preventDuplicates: !0,
        onclick: null,
        showDuration: "300",
        hideDuration: "1000",
        extendedTimeOut: "1000",
        showEasing: "swing",
        hideEasing: "linear",
        showMethod: "fadeIn",
        hideMethod: "fadeOut",
        tapToDismiss: !1,
    });
}

var btn = 0;
$('.more-btn').click(function () {
    if (btn == 0) {
        btn = 1;
        $(this).empty().text('مشاهده کمتر')
    } else {
        btn = 0;
        $(this).empty().text('مشاهده بیشتر');
    }
    $('#content').toggleClass('height-auto');
});

$("#CountrySearch").keyup(function () {
    // Retrieve the input field text and reset the count to zero
    var filter = $(this).val();
    count = 0;
    // Loop through the comment list
    $('.country-search').each(function () {
        // If the list item does not contain the text phrase fade it out
        if ($(this).text().search(new RegExp(filter, "i")) < 0) {
            $(this).hide();  // MY CHANGE
            // Show the list item if the phrase matches and increase the count by 1
        } else {
            $(this).show(); // MY CHANGE
            count++;
        }
    });
});

$("#ServiceSearch").keyup(function () {
    // Retrieve the input field text and reset the count to zero
    var filter = $(this).val();
    console.log('asd')
    count = 0;
    // Loop through the comment list
    $('.service-search').each(function () {
        // If the list item does not contain the text phrase fade it out
        if ($(this).text().search(new RegExp(filter, "i")) < 0) {
            $(this).hide();  // MY CHANGE
            // Show the list item if the phrase matches and increase the count by 1
        } else {
            $(this).show(); // MY CHANGE
            count++;
        }
    });
});
