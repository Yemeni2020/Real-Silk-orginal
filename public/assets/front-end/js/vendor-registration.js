'use strict';
$(document).ready(function() {
    $('.proceed-to-next-btn').click(function() {
        let type_account = $("input[name='account_type']:checked").val();

        if (type_account == "office") {
            $(".upload-file-control").parent().css("display", "none");
        }
        else{
            $(".upload-file-control").parent().css("display", "block");

        }
        let email = $('#email').val();
        let phone = $('.phone-input-with-country-picker').val();
        let Fullphone = $('#phone').val();
        let password = $('#password').val();
        let confirmPassword = $('#confirm_password').val();
        let referral_code = $('#referral_code').val();
        let emailPattern = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        let getErrorMessages = $('#proceed-to-next-validation-message');
        if (email === '') {
            $('.mail-error').html(getErrorMessages.data('mail-error'));
            return;
        } else {
            $('.mail-error').html('');
        }
        if (!emailPattern.test(email)) {
            $('.mail-error').html(getErrorMessages.data('valid-mail'));
            return;
        } else {
            $('.mail-error').html('');
        }
        if (phone === '') {
            $('.phone-error').html(getErrorMessages.data('phone-error'));
            return;
        } else {
            $('.phone-error').html('');
        }
        if (password === '') {
            $('.password-error').html(getErrorMessages.data('enter-password'));
            return;
        } else {
            $('.password-error').html('');
        }
        if (confirmPassword === '') {
            $('.confirm-password-error').html(getErrorMessages.data('enter-confirm-password'));
            return;
        } else {
            $('.confirm-password-error').html('');
        }
        if (password.trim() !== confirmPassword.trim()) {
            $('.confirm-password-error').html(getErrorMessages.data('password-not-match'));
            return;
        } else {
            $('.confirm-password-error').html('');
        }
        
        const getFormId =  'seller-registration-step1';
        
        // let formData = new FormData(document.getElementById(getFormId));
        let formData = new FormData();
        formData.append("email", email);
        formData.append("phone", Fullphone);
        formData.append("password", password);
        formData.append("confirm_password", confirmPassword);
        formData.append("_token", $('meta[name="csrf-token"]').attr('content'));

        let url = $('#seller-registration-step1').attr('action');
            $.ajaxSetup({
                headers: {
                    'X-XSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: $('#'+getFormId).attr('action'),
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $("#loading").removeClass("d--none");
                    $("#loading").addClass("d-grid");
                },
                success: function (data) {
                    if (data.errors) {
                        for (let index = 0; index < data.errors.length; index++) {
                            if(data.errors[index].error_code=="email")
                                $('.mail-error').html(data.errors[index].message);
                            if(data.errors[index].error_code=="phone")
                                $('.phone-error').html(data.errors[index].message);
                            toastr.error(data.errors[index].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                        $('.tio-refresh').click();
                    } else if(data.error){
                        toastr.error(data.error, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        $('.tio-refresh').click();
                    }else {

                        // ✅ عرض الموديل عند نجاح الطلب
                        $('#verification-modal').modal('show'); 

                        // ✅ إخفاء النموذج الأول وإظهار الثاني إن لزم الأمر
                        toastr.success("Check Your Email");
                        // $('.first-el').fadeOut(300);
                        // $('.second-el').fadeIn(300);
                        // $('.tio-refresh').click();
                    }
                },error:function (xhr) {
                    // $("#loading").removeClass("d-grid");
                    // $("#loading").addClass("d--none");
                if (xhr.status === 422) { // خطأ التحقق من البيانات
                    let errors = xhr.responseJSON.errors;
                    let msg="";
                    if (errors.email) {
                        msg=errors.email[0];
                        $('.mail-error').html(msg); // عرض خطأ البريد
                    } else {
                        $('.mail-error').html('');
                    }

                    if (errors.phone) {
                        msg=errors.phone[0];
                        $('.phone-error').html(msg); // عرض خطأ الهاتف
                    } else {
                        $('.phone-error').html('');
                    }

                    toastr.error(msg, {
                        CloseButton: true,
                        ProgressBar: true
                    });

                } else {
                    toastr.error(msg, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
                },complete: function () {
                    $("#loading").removeClass("d-grid");
                    $("#loading").addClass("d--none");

                },
            })
        
    });
});
$('.back-to-main-page').on('click',function (){
    $('.first-el').fadeIn(300);
    $('.second-el').fadeOut(300);
});

function submitRegistration(){
    let getText = $('#get-confirm-and-cancel-button-text');
    const getFormId =  'seller-registration';
    Swal.fire({
        title: getText.data('sure'),
        text:  getText.data('message'),
        type: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        cancelButtonText: getText.data('cancel'),
        confirmButtonText: getText.data('confirm'),
        reverseButtons: true
    }).then((result) => {
        if (result.value) {

            let formData = new FormData(document.getElementById(getFormId));
            $.ajaxSetup({
                headers: {
                    'X-XSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
                }
            });
            $.post({
                url: $('#'+getFormId).attr('action'),
                data: formData,
                contentType: false,
                processData: false,
                beforeSend: function () {
                    $("#loading").addClass("d-grid");
                },
                success: function (data) {
                    $('span[error="f_name"]').html("");
                    $('span[error="l_name"]').html("");
                    $('span[error="shop_name"]').html("");
                    $('span[error="shop_address"]').html("");
                    $('span[error="image"]').html("");
                    $('span[error="logo"]').html("");
                    $('span[error="g-recaptcha-response"]').html("");
                    if (data.errors) {
                        for (let index = 0; index < data.errors.length; index++) {
                            let error_code=data.errors[index].error_code;
                            $('span[error="'+error_code+'"]').html(data.errors[index].message);
                            // if(data.errors[index].error_code=="f_name")
                            //     $('span[error="f_name"]').html(data.errors[index].message);
                            // if(data.errors[index].error_code=="l_name")
                            //     $('span[error="l_name"]').html(data.errors[index].message);
                            toastr.error(data.errors[index].message, {
                                CloseButton: true,
                                ProgressBar: true
                            });
                        }
                        $('.tio-refresh').click();
                    } else if(data.error){
                        toastr.error(data.error, {
                            CloseButton: true,
                            ProgressBar: true
                        });
                        $('.tio-refresh').click();
                    }else {
                        $('.registration-success-modal').modal('show');
                        setTimeout(function () {
                            location.href = data.redirectRoute;
                        }, 4000);
                        $('.tio-refresh').click();
                    }
                },complete: function () {
                    $("#loading").removeClass("d-grid");
                },
            })
        }
    })
}
$("#confirm-code").on('click', function () {
    let email = $("#email").val(); // جلب البريد الإلكتروني من الحقل
    let token = $("#verification-code").val(); // جلب رمز التحقق من الحقل

    if (!email || !token) {
        toastr.error("Please_enter_your_phonw");
        return;
    }

    $.ajax({
        type: "POST",
        url: $("#confirm-code").data("url"), // اجلب رابط الـ route من الزر نفسه
        data: {
            email: email,
            token: token,
            _token: $('meta[name="csrf-token"]').attr('content') // إضافة CSRF Token
        },
        success: function (response) {
            if (response.status) {
                toastr.success(response.message); // رسالة نجاح
                $(".verification-modal").modal("hide"); // إخفاء المودال عند النجاح
                $('.first-el').fadeOut(300);
                $('.second-el').fadeIn(300);
            } else {
                toastr.success(response.message);
            }
        },
        error: function (xhr) {
            toastr.error(xhr.responseText);
            console.error(xhr.responseText);
        }
    });
});

$('#terms-checkbox').on('click', function () {
    if ($(this).is(':checked')) {
        $('#vendor-apply-submit').removeAttr('disabled');
    } else {
        $('#vendor-apply-submit').attr('disabled', 'disabled');
    }
});
