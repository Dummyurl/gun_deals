<select id="record_per_page_select" class="form-control">
    <?php $array = array(20, 50, 100, 200, 500); ?>
    <?php foreach ($array as $a): ?>
        <option value="<?php echo $a; ?>" <?php echo $a == \Request::get('record_per_page') ? 'selected="selected"' : ''; ?>><?php echo $a; ?> Records Per Page</option>                
    <?php endforeach; ?>    
</select>                        
