"use strict";

$(document).ready(function () {
    $("#confirm_password2").on("keyup", function () {
        checkPasswordMatch();
    });

    initializePhoneInput('.profile-phone-with-country-picker', '.profile-phone-country-picker-hidden');
});

$(".profile-pic-upload").on('change', function () {
    $('.remove-img').removeClass('d-none')
});

$("#password2").on("keyup", function () {
    if ($( "#confirm_password2" ).val() != '') {
        checkPasswordMatch();
    }
});
