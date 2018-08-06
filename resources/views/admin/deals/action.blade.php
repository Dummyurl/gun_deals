<center>
<div class="btn-group">                
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
    
    @if($isView)
    <a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.show',['id' => $row->id]) }}" class="btn btn-xs btn-primary" title="View Deal">
        <i class="fa fa-eye"></i>
    </a>          
    @endif            
</div>
</center>