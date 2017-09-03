<div>
    <?php foreach ($items as $item): ?>
        <div class='list_item clearfix'>
            <div class='list_photo image_wrapper fl_l'>
                <img src="<?=$item['img']?>">
            </div>
            <div class='list_text_block'>
                <div><?=$item['name']?></div>
                <div><?=$item['price']?></div>
                <div><?=$item['description']?></div>
            </div>
        </div>
    <?php endforeach ?>
</div>