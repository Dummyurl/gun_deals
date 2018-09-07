<center>
<div class="btn-group">                
      
    @if($isView)
    <a data-id="{{ $row->id }}" href="{{ route($currentRoute.'.show',['id' => $row->id]) }}" class="btn btn-xs btn-warning" title="View" target="_blank">
        <i class="fa fa-eye"></i>
    </a>          
    @endif
 
</div>
</center>