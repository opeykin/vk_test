<form class="input-form" method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]); ?>">

    <?php
    include 'form_fields.php';
    ?>

    <div class="clearfix input-form-section">
        <input type="submit" class="button button-blue fl_r" value="Add">
    </div>

</form>