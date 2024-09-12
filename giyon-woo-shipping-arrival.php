<fieldset class="wc-block-checkout__shipping-option wp-block-woocommerce-checkout-arrival-methods-block wc-block-components-checkout-step" id="shipping-option">
    <legend class="screen-reader-text">Arrival options
    </legend>
    <div class="wc-block-components-checkout-step__heading">
        <h2 class="wc-block-components-title wc-block-components-checkout-step__title" aria-hidden="true">Arrival options
        </h2>
    </div>
    <div class="wc-block-components-checkout-step__container">
        <div class="wc-block-components-checkout-step__content">
            <div class="wc-block-components-notices">
            </div>
            <div class="wc-block-components-notices__snackbar wc-block-components-notice-snackbar-list" tabindex="-1">
                <div></div>
            </div>
            <div class="">
                <div class="" aria-hidden="false">
                    <div class="wc-block-components-shipping-rates-control css-0 e19lxcc00">
                        <div class="wc-block-components-shipping-rates-control__package wc-block-components-shipping-rates-control__package--last-selected">
                            <div class="wc-block-components-radio-control wc-block-components-radio-control--highlight-checked">
                                <?php foreach (['8:00 - 12:00', '12:00 - 14:00', '14:00 - 16:00', '16:00 - 18:00', '18:00 - 20:00', '19:00 - 21:00', '20:00 - 21:00'] as $hour): ?>
                                    <label class="wc-block-components-radio-control__option" for="<?= str_replace(' ', '_', $hour) ?>">
                                        <input id="<?= str_replace(' ', '_', $hour) ?>" class="wc-block-components-radio-control__input" type="radio" name="giyon_arrival_options" aria-describedby="<?= str_replace(' ', '_', $hour) ?>__label <?= str_replace(' ', '_', $hour) ?>__secondary-label" aria-disabled="false" value="<?= $hour ?>">
                                        <div class="wc-block-components-radio-control__option-layout">
                                            <div class="wc-block-components-radio-control__label-group">
                                                <span id="<?= str_replace(' ', '_', $hour) ?>__label" class="wc-block-components-radio-control__label">
                                                    <?= $hour ?>
                                                </span>
                                            </div>
                                        </div>
                                    </label>
                                <?php endforeach ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

</fieldset>