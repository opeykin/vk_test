<div id="item">
    <div class="clearfix">
        <div class="fl_l">
            <img class="item-photo" src="<?= empty($item['img']) ? 'img/placeholder.jpg' : $item['img'] ?>">
        </div>
        <div class='item-text-block'>
            <div class="item-title"><?= $item['name'] ?></div>
            <div class="item-price"><?= $item['price'] . ' $' ?></div>
            <div class="item-control">
                <a href="<?='edit.php?id='.$item['id']?>">edit</a>
            </div>
        </div>
    </div>
    <div class="item-description"><?= $item['description'] ?></div>
</div>