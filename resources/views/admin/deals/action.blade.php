<center>
<div class="btn-group txt-center">                
    <center>
    @if($isEdit)
    <a href="{{ route($currentRoute.'.edit',['id' => $row->id]) }}" class="btn btn-xs btn-primary" title="edit">
        <i class="fa fa-edit"></i>
    </a>         
    @endif

    
    @if($isView)
    <a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.show',['id' => $row->id]) }}" class="btn btn-xs btn-primary" title="View Deal">
        <i class="fa fa-eye"></i>
    </a>          
    @endif            

    @if($isDelete)
    <a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.destroy',['id' => $row->id]) }}" class="btn btn-xs btn-danger btn-delete-record" title="delete">
        <i class="fa fa-trash-o"></i>
    </a>          
    @endif            

    @if($row->is_featured == 1)
    
        <br /><br /><a href="{{ route($currentRoute.'.index',['id' => $row->id,'featured_action' => 2]) }}" class="btn btn-xs btn-danger" onclick="return confirm('Are you sure you want to remove this deal as featured ?');">Remove As Featured</a>
    
    @else
    
        <br /><br /><a href="{{ route($currentRoute.'.index',['id' => $row->id,'featured_action' => 1]) }}" class="btn btn-xs btn-success" onclick="return confirm('Are you sure you want to set this deal as featured ?');">Set As Featured</a>
    
    @endif
    </center>
</div>
</center>