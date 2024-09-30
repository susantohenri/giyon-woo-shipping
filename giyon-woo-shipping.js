setTimeout(() => {
    jQuery(`#payment`).addClass(`wd-table-wrapper`)
}, 1000)

if (0 == giyon_woo_shipping.order_id) {
    giyon_clear_local_storage()
    giyon_monitor_shipping_class()
    giyon_create_paylater_form()
    giyon_monitor_submit_button_require_paylater()
} else {
    let post_data = {
        order_id: giyon_woo_shipping.order_id
    }

    const arrival_hour = localStorage.getItem(`giyon-arrival-hour`)
    if (`null` != arrival_hour) post_data.arrival_hour = arrival_hour

    const paylater_time = localStorage.getItem(`giyon-paylater-time`)
    if (`null` != paylater_time) post_data.paylater_time = paylater_time

    jQuery.post(giyon_woo_shipping.upload_local_storage, post_data, () => {
        giyon_clear_local_storage()
    })
}

function giyon_monitor_shipping_class() {
    if (giyon_is_shipping_form_exists()) {
        if (giyon_is_box()) {
            if (giyon_is_selected()) giyon_create_arrival_form()
            else giyon_remove_arrival_form()
        } else giyon_remove_arrival_form()
    } else giyon_remove_arrival_form()

    setTimeout(giyon_monitor_shipping_class, 1000)
}

function giyon_is_shipping_form_exists() {
    return 0 < jQuery(giyon_woo_shipping.shipping_form_selector).length
}

function giyon_is_box() {
    if (1 > jQuery(giyon_woo_shipping.shipping_class_selector).length) return false
    return 0 == jQuery(giyon_woo_shipping.shipping_class_selector).html().indexOf(`Box`)
}

function giyon_is_selected() {
    return true
    const radio = jQuery(`[value="giyon_shipping"]`)
    if (1 > radio.length) return false
    return radio.is(`:checked`)
}

function giyon_is_arrival_form_exists() {
    return 0 < jQuery(giyon_woo_shipping.arrival_form_selector).length
}

function giyon_create_arrival_form() {
    if (!giyon_is_arrival_form_exists()) jQuery.get(giyon_woo_shipping.arrival_form, arrival_form => {
        jQuery(giyon_woo_shipping.shipping_form_selector).after(arrival_form)
        jQuery(giyon_woo_shipping.arrival_form_selector).find(`:radio`).click(function () {
            localStorage.setItem(`giyon-arrival-hour`, jQuery(this).val())
        })
    })
}

function giyon_remove_arrival_form() {
    if (giyon_is_arrival_form_exists()) jQuery(giyon_woo_shipping.arrival_form_selector).remove()
}

function giyon_create_paylater_form() {
    jQuery.get(giyon_woo_shipping.paylater_form, paylater_form => {
        jQuery(giyon_woo_shipping.shipping_form_selector).after(paylater_form)
        const date_input = jQuery(giyon_woo_shipping.paylater_form_selector).find(`[type="text"]`)

        date_input
            .datepicker({
                minDate: 0,
                defaultDate: new Date(),
                dateFormat: `dd-mm-yy`,
                onSelect: dateText => {
                    localStorage.setItem(`giyon-paylater-time`, dateText)
                }
            })
            .datepicker(`setDate`, `-0d`)

        jQuery(giyon_woo_shipping.paylater_form_selector).find(`:radio`).click(function () {
            const selected = jQuery(this).val()
            if (`Lainnya` == selected) {
                date_input.show()
            } else {
                date_input.hide()
                localStorage.setItem(`giyon-paylater-time`, selected)
            }
        })
    })
}

function giyon_monitor_submit_button_require_paylater() {
    if (0 < jQuery(giyon_woo_shipping.paylater_form_selector).find(`:radio:checked`).length) jQuery(`[name="woocommerce_checkout_place_order"]`).show()
    else jQuery(`[name="woocommerce_checkout_place_order"]`).hide()
    setTimeout(giyon_monitor_submit_button_require_paylater, 1000)
}

function giyon_clear_local_storage() {
    localStorage.setItem(`giyon-arrival-hour`, null)
    localStorage.setItem(`giyon-paylater-time`, null)
}