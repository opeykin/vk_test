<form method="post" action="<?php echo htmlspecialchars($_SERVER["PHP_SELF"]);?>">
    Name: <input name="name" <?php echo 'value="' . $_POST['name'] . '"'; ?>>*<br>
    Price: <input name="price"<?php echo 'value="' . $_POST['price'] . '"'; ?>>*<br>
    Image url: <input name="img"<?php echo 'value="' . $_POST['img'] . '"'; ?>><br>
    Description: <input name="description"<?php echo 'value="' . $_POST['description'] . '"'; ?>><br>
    <input type="submit" value="Add item">
</form>