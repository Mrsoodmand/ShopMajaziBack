$(document).on('click', '.buy-with-wallet', function () {
    var id = $(this).data('id');
    $.ajax({
        url: '{{url('user/payment/wallet')}}/5/0',
        dataType: 'JSON',
        method: 'GET',
        data: {
        country_id: $('#country_id').val(),
            plan_id: $('#plan_id').val(),
            volume_id: $('#volume_id').val(),
    },
    beforeSend: function () {
        $('.buy-with-wallet').prop('disabled', true);
        $('.buy-with-wallet').empty().text('لطفا صبر کنید...');
    },
    success: function (data) {
        success('سفارش شما با موفقیت پرداخت شد. درحال انتقال به صفحه جزئیات سفارش.')
        setTimeout(function () {
            location.href = data.url
        }, 2000);
    },
    error: function (err) {
        console.log(err)
        var Error = err.responseJSON.error;
        Error.forEach(function (value) {
            error(value);
        });
        $('.buy-with-wallet').prop('disabled', false);
        $('.buy-with-wallet').empty().text('خرید با کیف پول');
    }
});
});
$(document).ready(function () {
    $('#virtual-number').modal('toggle');
});
$(document).on('click', '.buy-with-wallet', function () {
    var id = $(this).data('id');
    $.ajax({
        url: '{{url('user/payment/wallet')}}/5/0',
        dataType: 'JSON',
        method: 'GET',
        data: {
        country_id: $('#country_id').val(),
            plan_id: $('#plan_id').val(),
            volume_id: $('#volume_id').val(),
    },
    beforeSend: function () {
        $('.buy-with-wallet').prop('disabled', true);
        $('.buy-with-wallet').empty().text('لطفا صبر کنید...');
    },
    success: function (data) {
        success('سفارش شما با موفقیت پرداخت شد. درحال انتقال به صفحه جزئیات سفارش.')
        setTimeout(function () {
            location.href = data.url
        }, 2000);
    },
    error: function (err) {
        console.log(err)
        var Error = err.responseJSON.error;
        Error.forEach(function (value) {
            error(value);
        });
        $('.buy-with-wallet').prop('disabled', false);
        $('.buy-with-wallet').empty().text('خرید با کیف پول');
    }
});
});
