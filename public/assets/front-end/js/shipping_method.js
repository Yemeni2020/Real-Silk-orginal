"use strict";

function getApi_shippingMethod(shop_id, select) {
    const shipping_method = select.value;
    const url = $("#route-checkout-shipping-method-api").data('route');

    if (!shipping_method) {
        console.error("Shipping method not selected");
        return;
    }

    const fullUrl = `${url}?shop_id=${shop_id}&shipping_method=${shipping_method}`;

    fetch(fullUrl)
        .then(response => {
            if (!response.ok) {
                throw new Error("Network response was not ok");
            }
            return response.text(); // إذا كانت الاستجابة HTML
        })
        .then(data => {
            const container = document.getElementById('quick-Api_shipping');
            if (container) {
                container.innerHTML = data;
                // إذا كنت تستخدم مكتبة مثل Bootstrap modal أو custom module
                const modal = document.querySelector("#Api_shipping");
                if (modal) {
                    // مثال: لو كنت تستخدم Bootstrap
                    // new bootstrap.Modal(modal).show();

                    // أو لو تستخدم custom function:
                    module("#Api_shipping").show();
                } else {
                    console.warn("Modal element #Api_shipping not found.");
                }
            } else {
                console.warn("Container element #quick-Api_shipping not found.");
            }
        })
        .catch(error => {
            console.error("Error fetching shipping method data:", error);
        });
}
