<div class="wd-table-wrapper wc-block-checkout__shipping-option wp-block-woocommerce-checkout-arrival-methods-block wc-block-components-checkout-step" id="shipping-option">
    <table class="shop_table">
        <thead>
            <tr>
                <th colspan="2" style="text-align: left;">Jam Kedatangan</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: left;">
                    <?php foreach (['8:00 - 12:00', '12:00 - 14:00', '14:00 - 16:00', '16:00 - 18:00', '18:00 - 20:00', '19:00 - 21:00'] as $hour): ?>
                        <label class="wc-block-components-radio-control__option" for="<?= str_replace(' ', '_', $hour) ?>">
                            <input id="<?= str_replace(' ', '_', $hour) ?>" class="wc-block-components-radio-control__input" type="radio" name="giyon_arrival_options" aria-describedby="<?= str_replace(' ', '_', $hour) ?>__label <?= str_replace(' ', '_', $hour) ?>__secondary-label" aria-disabled="false" value="<?= $hour ?>" style="margin-inline-end:0;">
                            <span id="<?= str_replace(' ', '_', $hour) ?>__label" class="wc-block-components-radio-control__label">
                                <?= $hour ?>
                            </span>
                        </label>
                    <?php endforeach ?>
                </td>
            </tr>
        </tbody>
    </table>
</div>