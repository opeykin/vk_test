<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    Name:
    <input name="name" <?php echo 'value="' . $params['name'] . '"'; ?>>
    *
    <span><?=$params['name_error']?></span>
    <br>

    Price:
    <input name="price"<?php echo 'value="' . $params['price'] . '"'; ?>>
    *
    <span><?=$params['price_error']?></span>
    <br>

    Image url:
    <input name="img"<?php echo 'value="' . $params['img'] . '"'; ?>>
    <span><?=$params['img_error']?></span>
    <br>

    Description:
    <input name="description"<?php echo 'value="' . $params['description'] . '"'; ?>>
    <span><?=$params['description_error']?></span>
    <br>

    <input type="hidden" name="id" value="<?=$params['id']?>">

    <input type="submit" value="Submit changes">
</form>