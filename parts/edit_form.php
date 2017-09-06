<form class="input-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

    <?php
    include 'form_fields.php';
    ?>

    <input type="hidden" name="id" value="<?= $params['id'] ?>">

    <div class="clearfix input-form-section">
        <a href="<?='delete.php?id='.$params['id']?>" class="button button-red fl_l">Delete</a>
        <input type="submit" class="button button-blue fl_r" value="Submit changes">
    </div>
</form>