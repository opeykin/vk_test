<div id="items_list">
    <?php foreach ($items as $item): ?>
        <div class='list_item clearfix'>
            <div class='list_photo image_wrapper fl_l'>
                <a href="<?='item.php?id='.$item['id']?>">
                    <img src="<?=empty($item['img']) ? 'img/placeholder.jpg' : $item['img']?>">
                </a>
            </div>
            <div class='list_text_block'>
                <a href="<?='item.php?id='.$item['id']?>" class="list_title"><?=$item['name']?></a>
                <div class="list_price"><?=$item['price'] . ' $'?></div>
                <div class="list_description crop"><?=$item['description']?></div>
            </div>
        </div>
    <?php endforeach ?>
</div>