giyon_monitor_free_shipping_class()
function giyon_monitor_free_shipping_class() {
    const shipping_label = jQuery(`label[for="shipping_method_0_giyon_shipping"]`)
    if (0 < shipping_label.length) {
        const amount = shipping_label.find(`.amount`)
        if (1 > amount.length) {
            shipping_label.append(`
                <span class="woocommerce-Price-amount amount">
                    Free
                </span>
            `)
        }
    }
    setTimeout(giyon_monitor_free_shipping_class, 1000)
}