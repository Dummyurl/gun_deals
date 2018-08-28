<center>
<div class="btn-group">                
    @if($isEdit)
    <a href="{{ route($currentRoute.'.edit',['id' => $row->id]) }}" class="btn btn-xs btn-primary" title="Edit">
        <i class="fa fa-edit"></i>
    </a>         
    @endif

    @if($isDelete)
    <a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.destroy',['id' => $row->id]) }}" class="btn btn-xs btn-danger btn-delete-record" title="Delete">
        <i class="fa fa-trash-o"></i>
    </a>          
    @endif            
</div>
</center>