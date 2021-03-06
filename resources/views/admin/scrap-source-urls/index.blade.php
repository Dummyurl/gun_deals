@extends('admin.layouts.app')


@section('content')

<!-- BEGIN PAGE CONTENT BODY -->
<div class="page-content">
    <div class="container">

        <div class="col-md-12">
            @include($moduleViewName.".search")   
            <div class="clearfix">&nbsp;</div>    
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
                            <tr>
                                <th width="8%">ID</th>                                   
                                <th width="20%">Scrape Source</th>
                                <th width="35%">Scrape Url</th>
                                <th width="08%">Status</th>                           
                                <th width="12%">#Last Scan Date<br />#Data Counter</th>
                                <th width="10%">Created At</th>                           
                                <th width="7%">Action</th>
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
@section('styles')

@endsection

@section('scripts')
<script type="text/javascript">


    $(document).ready(function () {


        $("#search-frm").submit(function () {
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
                "data": function (data)
                {
                    data.search_category = $("#search-frm select[name='search_category']").val();
                }
            },
            "order": [[0, "desc"]],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'source_title', name: 'source_title'},
                {data: 'scrap_url', name: '{{ TBL_SCRAP_URLS }}.scrap_url'},
                {data: 'status', name: '{{ TBL_SCRAP_URLS }}.status'},
                {data: 'last_scan_date', name: '{{ TBL_SCRAP_URLS }}.last_scan_date'},
                {data: 'created_at', name: '{{ TBL_SCRAP_URLS }}.created_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@endsection