<?php
$edit_url = 'countries.edit';
$delete_url = 'countries.destroy';

?>
<td class="text-center">
    <div class="btn-group">
                
        <a href="{{ route($edit_url,array('id' => $row->id))}}" class="btn btn-xs btn-primary" title="Edit">
            <i class="fa fa-edit"></i>
        </a>         
        <a data-id="{{ $row->id }}" href="{{ route($delete_url,array('id' => $row->id))}}" class="btn btn-xs btn-danger btn-delete-record" title="Delete">
            <i class="fa fa-trash-o"></i>
        </a>                                                                            
    </div>
</td>