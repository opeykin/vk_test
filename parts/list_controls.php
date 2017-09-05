<?php
$order = (int)($_GET['order'] ?? 0);
?>

<div id="list_controls" class="clearfix">
    <a class="fl_l button button-blue" href="add.php">Add new</a>
    <select class="fl_r sorting_selector" onchange="document.location.href='?order='+this.selectedIndex">
        <option <?=$order == 0 ? 'selected' : ''?>>Cheap</option>
        <option <?=$order == 1 ? 'selected' : ''?>>Expensive</option>
        <option <?=$order == 2 ? 'selected' : ''?>>Old</option>
        <option <?=$order == 3 ? 'selected' : ''?>>New</option>
    </select>
</div>
