
$(document).on('click', '.sing-in', function () {
    $('.tab-pane').removeClass('show active');
    $('#signin-tab').addClass('show active');
    $('#signin-tab').show();
    $('#login-mobile').val('');
    $('#login-mobile').prop('placeholder', '09123450000');
    $('#Login-tab-header').empty().append('<i class="ci-unlocked me-2 mt-n1"></i>ورود یا ثبت نام');
});
$(document).ready(function () {
    $('button').prop('disabled', false);
    $('.login-btn').click(function () {
        var BtnId = $(this).attr('id');
        var text = $(this).data('text');
        var formName = $(this).data('form');
        $('#' + BtnId).html('لطفا صبر کنید...');
        $('#' + BtnId).attr('disabled', true);
        var form = document.querySelector('#' + formName);
        $(form).ajaxSubmit({
            dataType: 'json',
            beforeSend: function () {
                $('#' + BtnId).html('لطفا صبر کنید');
                $('.errors').hide();
            },
            success: function (data) {
                if (formName == 'signin-tab') {
                    if (data.header != null) {
                        $('#Login-tab-header').empty().append(data.header);
                    }
                    $('#signin-tab').hide();
                    $('#' + data.type + '-tab').addClass('active show');
                    $('.user-mobile').val($('#login-mobile').val());
                    if (data.type == 'register') {
                        $('#time-area').empty().append('<span class="register-number" data-seconds-left="' + data.time + '" style="position: absolute;left: 21px;display: flex;direction: ltr;"></span>');
                        $('.register-number').startTimer();
                        var time = data.time * 1000;
                        setTimeout(function () {
                            $('#new-code-request').show();
                        }, time);
                    }
                } else if (formName == 'register-tab') {
                    $('#signin-modal').modal('toggle');
                    success('ثبت نام با موفقیت انجام شد.');
                    location.reload();
                } else if (formName == 'login-tab') {
                    $('#signin-modal').modal('toggle');
                    success('عملیات ورود با موفقیت انجام شد.');
                    location.reload();
                }
                $('#' + BtnId).html(text);
                $('#' + BtnId).attr('disabled', false);
            },
            error: function (err) {
                for (const [key, value] of Object.entries(err.responseJSON.error)) {
                    $('#'+formName+' .'+ key).empty().text(value).show();
                }
                $('#' + BtnId).html(text);
                $('#' + BtnId).attr('disabled', false);
            }
        });
        return false;
    });
});
$(document).on('click', '.request-new-code', function () {
    $.ajax({
        url: 'https://shopmajazi.com//login/new-register-code',
        dataType: "JSON",
        method: 'GET',
        data: {
            mobile: $('.user-mobile').val(),
        },
        success: function (data) {
            success('کد جدید با موفقیت ارسال شد.');
            $('#time-area').empty().append('<span class="register-number" data-seconds-left="120" style="position: absolute;left: 21px;display: flex;direction: ltr;"></span>');
            $('.register-number').startTimer();
            $('#new-code-request').hide();
            setTimeout(function () {
                $('#new-code-request').show();
            }, 120000);
        }, error: function (err) {
            for (const [key, value] of Object.entries(err.responseJSON.error)) {
                error(value);
            }
        }
    });
});
function ChangeTab(oldTab, newTab) {
    $('#' + oldTab).removeClass('show active');
    $('#' + newTab).show();
    $('#signin-modal .form-control').val('');
}
