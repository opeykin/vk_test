<?php
$order = (int)($_GET['order'] ?? 0);
?>

<div class="clearfix">
    <div class="fl_l">I want to see</div>
    <select onchange="document.location.href='?order='+this.selectedIndex">
        <option <?=$order == 0 ? 'selected' : ''?>>Cheap</option>
        <option <?=$order == 1 ? 'selected' : ''?>>Expensive</option>
        <option <?=$order == 2 ? 'selected' : ''?>>Old</option>
        <option <?=$order == 3 ? 'selected' : ''?>>New</option>
    </select>
</div>
