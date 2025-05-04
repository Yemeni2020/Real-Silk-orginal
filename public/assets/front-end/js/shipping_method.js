"use strict";
function getApi_shippingMethod(shop_id, select) {
    const shipping_method = select.value;
    const url = $("#route-checkout-shipping-method-api").data('route');

    // إضافة المعاملات للطلب (shop_id + shipping_method)
    const fullUrl = `${url}?shop_id=${shop_id}&shipping_method=${shipping_method}`;

    fetch(fullUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.text(); // أو response.json() حسب نوع الاستجابة
        })
        .then(data => {
            // ضع النتائج في العنصر المناسب
            console.log(data);
            document.getElementById('Api_shipping' + shop_id).innerHTML = data;
        })
        .catch(error => {
            console.error("Error fetching shipping method data:", error);
            document.getElementById('Api_shipping' + shop_id).innerHTML = "<p>حدث خطأ أثناء تحميل البيانات.</p>";
        });
}