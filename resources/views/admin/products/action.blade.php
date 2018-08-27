<center>
<div class="btn-group">                
    @if($isEdit)
    <a href="{{ route($currentRoute.'.edit',['id' => $row->id]) }}" class="btn btn-xs btn-primary" title="Edit">
        <i class="fa fa-edit"></i>
    </a>         
    @endif

    @if($isDelete)
    <a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.destroy',['id' => $row->id]) }}" class="btn btn-xs btn-danger btn-delete-record" title="delete">
        <i class="fa fa-trash-o"></i>
    </a>          
    @endif            
    
    @if($isView)
    <a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.show',['id' => $row->id]) }}" class="btn btn-xs btn-warning" title="View">
        <i class="fa fa-eye"></i>
    </a>          
    @endif            

    @if($isDeals)
    <a target="_blank" data-id="{{ $row->id }}" href="{{ url("admin/deals?search_product_id=".$row->id) }}" class="btn btn-xs btn-success" title="View Deals">
        <i class="fa fa-list"></i>
    </a>          
    @endif            
</div>
</center>