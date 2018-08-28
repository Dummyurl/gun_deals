@extends('admin.layouts.app')


@section('content')

<!-- BEGIN PAGE CONTENT BODY -->
<div class="page-content">
    <div class="container">

        <div class="col-md-12">

            @include($moduleViewName.".search")   

            <div class="clearfix"></div>    
            <div class="portlet box green">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-list"></i>{{ $page_title }}    
                    </div>

                    @if($btnAdd)
                    <a class="btn btn-default pull-right btn-sm mTop5" href="{{ $add_url }}">Add New</a>
                    @endif 

                </div>
                <div class="portlet-body">                    
                    <table class="table table-bordered table-striped table-condensed flip-content" id="server-side-datatables">
                        <thead>
                                   <th width="10%">ID</th>  
                                   <th width="30%">CronName</th>                           
                                   <th width="30%">Cron Interval</th>                           
                                   <th width="20%">Created At</th>                           
                                   <th width="10%" data-orderable="false">Action</th>                            
                        </thead>                                         
                        <tbody>
                        </tbody>
                    </table>                                              
                </div>
            </div>              
        </div>
    </div>
</div>
</div>            
@endsection
@section('styles')

@endsection

@section('scripts')
<script type="text/javascript">


    $(document).ready(function () {

        $("#search-frm").submit(function(){
            oTableCustom.draw();
            return false;
        });


        $.fn.dataTableExt.sErrMode = 'throw';

        var oTableCustom = $('#server-side-datatables').DataTable({
            processing: true,
            serverSide: true,
            searching: false,
            ajax: {
                "url": "{!! route($moduleRouteText.'.data') !!}",
                "data": function ( data ) 
                {
                    data.search_start_date = $("#search-frm input[name='search_start_date']").val();
                    data.search_end_date = $("#search-frm input[name='search_end_date']").val();
                    data.search_id = $("#search-frm input[name='search_id']").val();
                    data.search_name = $("#search-frm input[name='search_name']").val();
                    data.search_url = $("#search-frm input[name='search_url']").val();
                    data.search_interval = $("#search-frm input[name='search_interval']").val();
                }
            },            
            "order": [[ 0, "desc" ]],    
            columns: [
                { data: 'id', name: 'cron_log.id' },
                { data: 'cron_name', name: 'cron_log.cron_name' },                        
                { data: 'cron_interval', name: 'cron_log.cron_interval'},        
                { data: 'created_at', name: 'cron_log.created_at' },        
                { data: 'action', orderable: false, searchable: false}             
            ]
        });        

    });
</script>
@endsection