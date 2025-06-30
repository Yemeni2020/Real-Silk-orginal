"use strict";

function getApi_shippingMethod(shop_id, select) {
    const shipping_method = select.value;
    const url = $("#route-checkout-shipping-method-api").data('route');

    if (!shipping_method) {
        console.error("Shipping method not selected");
        return;
    }
    const container = document.getElementById('Api_shipping' + shop_id);

    $.ajax({
        url: url,
        type: "GET",
        data: {
            shop_id: shop_id,
            shipping_method: shipping_method
        },
        beforeSend: function() {
            container.innerHTML = "";
            $("#loading").show();
        },
        success: function(data) {
            if (container) {
                container.innerHTML = data;

                // const modal = document.querySelector("#Api_shipping");
                // if (modal) {
                //     $("#Api_shipping").modal("show");
                // } else {
                //     console.warn("Modal element #Api_shipping not found.");
                // }
            } else {
                console.warn("Container element #quick-Api_shipping not found.");
            }
        },
        error: function(xhr, status, error) {
            console.error("Error fetching shipping method data:", error);
        },
        complete: function() {
            $("#loading").hide();
        }
    });

}
window.shippingMethodPriceSummary = {};
window.total_amount = parseFloat(window.cat_value_price.value) || 0;

function selectShippingMethod(index, shop_id) {
    // إزالة التنسيق النشط من جميع الخيارات
    document.querySelectorAll('input[name="shipping_method' + shop_id + '"]').forEach((input) => {
        const label = document.querySelector(`label[for="${input.id}"]`);
        if (label) label.classList.remove('active');
    });

    // إضافة التنسيق النشط للعنصر المحدد
    const selectedInput = document.getElementById(`shipping_${index}_${shop_id}`);
    const selectedLabel = document.querySelector(`label[for="shipping_${index}_${shop_id}"]`);
    const PriceShipping = document.getElementById(`price_shipping_${index}_${shop_id}`);

    if (PriceShipping && !isNaN(parseFloat(PriceShipping.value))) {
        window.shippingMethodPriceSummary[shop_id] = parseFloat(PriceShipping.value);
    }

    updateTotalShippingPrice();

    if (selectedInput) selectedInput.checked = true;
    if (selectedLabel) selectedLabel.classList.add('active');
}

function updateTotalShippingPrice() {
    let total = 0;
    for (const shopId in window.shippingMethodPriceSummary) {
        const price = window.shippingMethodPriceSummary[shopId];
        if (!isNaN(price)) {
            total += price;
        }
    }

    const totalElement = document.getElementById('shippingMethodPriceSummary');
    const cat_value_price = document.getElementById('cat_value_price');
    if (totalElement) {
        totalElement.innerText = formatCurrency(total.toFixed(2));
    }

    // تحديث السعر النهائي داخل input cat_value_price
    if (window.cat_value_price) {
        window.cat_value_price.innerHTML = formatCurrency(total + parseFloat(document.getElementById('total_invoice').value)); // <-- نستخدم دالة للتنسيق
    }
}

function formatCurrency(amount) {
    return new Intl.NumberFormat('en', {
        style: 'currency',
        currency: 'SAR' // ← عدلها حسب العملة لديك
    }).format(amount);
}

function checkoutFromShippingMethod() {
    let physical_product = $('#physical_product').val();
    let billing_address_same_shipping;

    if (physical_product === 'yes') {
        let sameAsShippingCheckbox = $('#same_as_shipping_address');
        billing_address_same_shipping = sameAsShippingCheckbox ? sameAsShippingCheckbox.is(":checked") : false;

        let allAreFilled = true;
        document.getElementById("address-form").querySelectorAll("[required]").forEach(function(i) {
            if (!allAreFilled) return;
            if (!i.value) allAreFilled = false;
            if (i.type === "radio") {
                let radioValueCheck = false;
                document.getElementById("address-form").querySelectorAll(`[name=${i.name}]`).forEach(function(r) {
                    if (r.checked) radioValueCheck = true;
                });
                allAreFilled = radioValueCheck;
            }
        });

        let allAreFilled_shipping = true;

        let billingAddressForm = $('#billing-address-form');
        if (billing_address_same_shipping != true && billingAddressForm.length > 0) {

            document.getElementById("billing-address-form").querySelectorAll("[required]").forEach(function(i) {
                if (!allAreFilled_shipping) return;
                if (!i.value) allAreFilled_shipping = false;
                if (i.type === "radio") {
                    let radioValueCheck = false;
                    document.getElementById("billing-address-form").querySelectorAll(`[name=${i.name}]`).forEach(function(r) {
                        if (r.checked) radioValueCheck = true;
                    });
                    allAreFilled_shipping = radioValueCheck;
                }
            });
        }
    } else {
        billing_address_same_shipping = false;
    }

    let isCheckCreateAccount = $('#is_check_create_account');
    let customerPassword = $('#customer_password');
    let customerConfirmPassword = $('#customer_confirm_password');

    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="_token"]').attr('content')
        }
    });
    $.post({
        url: $('#route-customer-choose-shipping-address-other').data('url'),
        data: {
            physical_product: physical_product,
            shipping: physical_product === 'yes' ? $('#address-form').serialize() : null,
            billing: $('#billing-address-form').serialize(),
            billing_addresss_same_shipping: billing_address_same_shipping,
            is_check_create_account: isCheckCreateAccount && isCheckCreateAccount.prop("checked") ? 1 : 0,
            customer_password: customerPassword ? customerPassword.val() : null,
            customer_confirm_password: customerConfirmPassword ? customerConfirmPassword.val() : null,
        },

        beforeSend: function() {
            $('#loading').show();
        },
        success: function(data) {
            // console.log(errors)
            // console.log(data.errors)
            if (data.errors) {
                for (var i = 0; i < data.errors.length; i++) {
                    toastr.error(data.errors[i].message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            } else {
                location.href = $('#route-shipping-method').data('url');
            }
        },
        complete: function() {
            $('#loading').hide();
        },
        error: function(data) {
            if (data.errors) {
                for (var i = 0; i < data.errors.length; i++) {
                    toastr.error(data.errors[i].message, {
                        CloseButton: true,
                        ProgressBar: true
                    });
                }
            } else {
                let error_msg = data.responseJSON.errors;
                console.log(data.responseJSON)
                toastr.error(error_msg, {
                    CloseButton: true,
                    ProgressBar: true
                });
            }
        }
    });
}