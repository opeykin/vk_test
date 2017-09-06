<div class="input-form-section">
    <label>Name:</label>
    <span class="required"> * </span>
    <span class="error"><?=$params['name_error']?></span>
    <input class="field" name="name" <?php echo 'value="' . $params['name'] . '"'; ?>>
</div>

<div class="input-form-section">
    <label>Price:</label>
    <span class="required"> * </span>
    <span class="error"><?=$params['price_error']?></span>
    <input class="field" name="price"<?php echo 'value="' . $params['price'] . '"'; ?>>
</div>

<div class="input-form-section">
    <label>Image url: </label>
    <span class="error"><?=$params['img_error']?></span>
    <input class="field" name="img"<?php echo 'value="' . $params['img'] . '"'; ?>>
</div>

<div class="input-form-section">
    <label>Description: </label>
    <span class="error"><?=$params['description_error']?></span>
    <textarea class="text-filed" name="description" ><?=$params['description']?></textarea>
</div>