@extends('admin.layouts.app')

@section('content')

<!-- BEGIN PAGE CONTENT BODY -->
<div class="page-content">
    <div class="container">

        <div class="col-md-12">
                @include($moduleViewName.".log_search")
            <div class="clearfix"></div>    
            <div class="portlet box green">
                <div class="portlet-title">
                    <div class="caption">
                        <i class="fa fa-list"></i>{{ $page_title }}    
                    </div>
                                 
                </div>
                <div class="portlet-body">                    
                        <table class="table table-bordered table-striped table-condensed flip-content" id="server-side-datatables">
                            <thead>
                                <tr>
                                   <th width="5%">ID</th>  
                                   <th width="5%">CronName</th>                           
                                   <th width="15%">Start Time</th>                           
                                   <th width="15%">End Time</th>                           
                                   <th width="15%">Total Time</th>                           
                                   <th width="25%">Summary</th>                           
                                   <th width="10%">Machin Name</th>                           
                                </tr>
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

@section('scripts')
    <script type="text/javascript">

    $(document).ready(function(){    
        
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
                "url": "{!! route('cron-log.data') !!}",
                "data": function ( data ) 
                {
                    data.search_start_date = $("#search-frm input[name='search_start_date']").val();
                    data.search_end_date = $("#search-frm input[name='search_end_date']").val();
                    data.search_id = $("#search-frm input[name='search_id']").val();
                    data.search_name = $("#search-frm select[name='search_name']").val();
                    data.search_summary = $("#search-frm input[name='search_summary']").val();
                    data.search_machine = $("#search-frm input[name='search_machine']").val();
                }
            },            
            "order": [[ 0, "desc" ]],    
            columns: [
                { data: 'id', name: 'id' },
                { data: 'name', name: 'cron_log.cron_name' },        
                { data: 'start_time', name: 'start_time' },          
                { data: 'end_time', name: 'end_time' },        
                { data: 'total_time', name: 'end_time' },        
                { data: 'summary', name: 'summary' },       
                { data: 'machine_id', name: 'machine_id' },       
                        
            ]
        });        
    });

    </script>

@endsection

