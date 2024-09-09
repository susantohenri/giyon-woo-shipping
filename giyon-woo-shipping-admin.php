<?php

if (isset($_FILES['giyon-woo-shipping-csv'])) {
    move_uploaded_file($_FILES['giyon-woo-shipping-csv']['tmp_name'], plugin_dir_path(__FILE__) . 'giyon-woo-shipping.csv');
}

if (isset($_POST['giyon_boxc_enable'])) update_option('giyon_boxc_enable', $_POST['giyon_boxc_enable']);
$giyon_boxc_enable = giyon_boxc_status();

if (isset($_POST['giyon_ongkir_cod'])) update_option('giyon_ongkir_cod', $_POST['giyon_ongkir_cod']);
$giyon_ongkir_cod = giyon_ongkir_cod();

if (isset($_POST['giyon_volume_smart_letter'])) update_option('giyon_volume_smart_letter', $_POST['giyon_volume_smart_letter']);
$giyon_volume_smart_letter = giyon_volume_smart_letter();

if (isset($_POST['giyon_volume_letter_pack_light'])) update_option('giyon_volume_letter_pack_light', $_POST['giyon_volume_letter_pack_light']);
$giyon_volume_letter_pack_light = giyon_volume_letter_pack_light();

if (isset($_POST['giyon_volume_letter_pack_plus'])) update_option('giyon_volume_letter_pack_plus', $_POST['giyon_volume_letter_pack_plus']);
$giyon_volume_letter_pack_plus = giyon_volume_letter_pack_plus();

?>
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-QWTKZyjpPEjISv5WaRU9OFeRpok6YctnYmDr5pNlyT2bRjXh0JMhjY6hW+ALEwIH" crossorigin="anonymous">
<div class="container">
    <div class="row mt-5 mb-5">
        <div class="col-12 text-center">
            <h1>Giyon Woo Shipping Configuration</h1>
        </div>
    </div>
    <div class="row">
        <div class="col-12">
            <form method="POST" enctype="multipart/form-data">
                <div class="mb-5">
                    <label class="form-label">Upload Ongkir CSV</label>
                    <input type="file" name="giyon-woo-shipping-csv" class="form-control" style="width: auto;">
                    <div class="form-text">
                        <a download href="<?= site_url('wp-content/plugins/giyon-woo-shipping/giyon-woo-shipping.csv') ?>">Download Current Active CSV</a>
                    </div>
                </div>
                <div class="mb-5">
                    <label class="form-label">BoxC Status</label>
                    <select name="giyon_boxc_enable" class="form-control">
                        <option value="1" <?= 1 == $giyon_boxc_enable ? 'selected' : '' ?>>Enabled</option>
                        <option value="0" <?= 0 == $giyon_boxc_enable ? 'selected' : '' ?>>Disabled</option>
                    </select>
                </div>
                <div class="mb-5">
                    <label class="form-label">Ongkir COD</label>
                    <input type="text" name="giyon_ongkir_cod" value="<?= $giyon_ongkir_cod ?>" class="form-control" style="width: 100px; text-align: right;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Volume Smart Letter</label>
                    <input type="text" name="giyon_volume_smart_letter" value="<?= $giyon_volume_smart_letter ?>" class="form-control" style="width: 100px; text-align: right;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Volume Letter Pack Light</label>
                    <input type="text" name="giyon_volume_letter_pack_light" value="<?= $giyon_volume_letter_pack_light ?>" class="form-control" style="width: 100px; text-align: right;">
                </div>
                <div class="mb-3">
                    <label class="form-label">Volume Letter Pack Plus</label>
                    <input type="text" name="giyon_volume_letter_pack_plus" value="<?= $giyon_volume_letter_pack_plus ?>" class="form-control" style="width: 100px; text-align: right;">
                </div>
                <button type="submit" class="btn btn-primary">Submit</button>
            </form>
        </div>
    </div>
</div>