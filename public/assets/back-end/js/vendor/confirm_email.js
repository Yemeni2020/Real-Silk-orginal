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
                // $(".verification-modal").modal("hide"); // إخفاء المودال عند النجاح
                location.reload();
            } else {
                $("#error-verification-code").html(response.message);
                toastr.error(response.message);
            }
        },
        error: function (xhr) {
            toastr.error(xhr.responseText);
            console.error(xhr.responseText);
        }
    });
});


$('.proceed-to-next-btn').click(function() {
    
    let email = $('#email').val();
    
    
    const getFormId =  'seller-registration-step1';
    
    // let formData = new FormData(document.getElementById(getFormId));
    let formData = new FormData();
    formData.append("email", email);
   
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
                } else if(data.error){
                    toastr.error(data.error, {
                        CloseButton: true,
                        ProgressBar: true
                    });
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
