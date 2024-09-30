<div class="wd-table-wrapper wc-block-checkout__shipping-option wp-block-woocommerce-checkout-paylater-methods-block wc-block-components-checkout-step" id="shipping-option">
    <table class="shop_table">
        <thead>
            <tr>
                <th colspan="2" style="text-align: left;">Waktu Pembayaran (harus diisi)</th>
            </tr>
        </thead>
        <tbody>
            <tr>
                <td style="text-align: left;">
                    <?php foreach (['Hari ini', 'Besok', 'Lainnya'] as $hour): ?>
                        <label class="wc-block-components-radio-control__option" for="<?= str_replace(' ', '_', $hour) ?>">
                            <input id="<?= str_replace(' ', '_', $hour) ?>" class="wc-block-components-radio-control__input" type="radio" name="giyon_paylater_options" aria-describedby="<?= str_replace(' ', '_', $hour) ?>__label <?= str_replace(' ', '_', $hour) ?>__secondary-label" aria-disabled="false" value="<?= $hour ?>" style="margin-inline-end:0;">
                            <span id="<?= str_replace(' ', '_', $hour) ?>__label" class="wc-block-components-radio-control__label">
                                <?= $hour ?>
                            </span>
                        </label>
                    <?php endforeach ?>
                    <input type="text" style="color: black; text-align: right; display: none;">
                </td>
            </tr>
        </tbody>
    </table>
</div>