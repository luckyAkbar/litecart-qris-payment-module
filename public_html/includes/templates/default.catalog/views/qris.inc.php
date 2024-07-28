<main id="qris-payment" style="text-align: center; display: flex; flex-direction: column; align-items: center; justify-content: center;">
    <?php if (!empty($title)) { ?>
        <h1><?php echo $title ?></h1>
    <?php } ?>
    <?php if (!empty($hint)) { ?>
        <p><?php echo $hint ?></p>
    <?php } ?>
    <?php if (!empty($expected_amount)) { ?>
        <p><?php echo $expected_amount ?></p>
    <?php } ?>
    <img src="<?php echo $image_path ?>" alt="QRIS Code" style="max-width: 100%; height: auto;">
    <?php if (!empty($render_submit_btn)) { ?>
        <form method="post" action="<?php echo document::ilink('order_process'); ?>">
            <button type="submit" name="confirm_payment" class="btn btn-default">Confirm Payment</button>
        </form>
    <?php } ?>
</main>