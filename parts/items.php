<div id="items_list">
    <?php foreach ($items as $item): ?>
        <div class='list_item clearfix'>
            <div class='list_photo image_wrapper fl_l'>
                <img src="<?=empty($item['img']) ? 'img/placeholder.jpg' : $item['img']?>">
            </div>
            <div class='list_text_block'>
                <div class="list_title"><?=$item['name']?></div>
                <div class="list_price"><?=$item['price'] . ' $'?></div>
                <div class="list_description crop"><?=$item['description']?></div>
            </div>
        </div>
    <?php endforeach ?>
</div>