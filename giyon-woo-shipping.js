if (0 == giyon_woo_shipping.order_id) giyon_monitor_shipping_class()
else {
    const stored = localStorage.getItem(`giyon-arrival-hour`)
    if (`null` != stored) jQuery.post(giyon_woo_shipping.upload_arrival_hour, {
        order_id: giyon_woo_shipping.order_id,
        arrival_hour: stored
    }, () => {
        localStorage.setItem(`giyon-arrival-hour`, null)
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