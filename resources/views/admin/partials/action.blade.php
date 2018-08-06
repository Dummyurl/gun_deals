@if(isset($postComment) && $postComment)
<a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.comment',['id' => $row->id]) }}" class="btn btn-xs btn-success" title="comment">
    <i class="fa fa-comments"></i>
</a>          
@endif

@if($isEdit)
<a href="{{ route($currentRoute.'.edit',['id' => $row->id]) }}" class="btn btn-xs btn-primary" title="edit">
    <i class="fa fa-edit"></i>
</a>         
@endif

@if($isDelete)
<a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.destroy',['id' => $row->id]) }}" class="btn btn-xs btn-danger btn-delete-record" title="delete">
    <i class="fa fa-trash-o"></i>
</a>          
@endif

