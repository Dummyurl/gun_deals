@extends('admin.layouts.app')


@section('content')

<!-- BEGIN PAGE CONTENT BODY -->
<div class="page-content">
    <div class="container">

        <div class="col-md-12">

            @include($moduleViewName.".search")   

            <div class="clearfix"></div>    
            <a class="btn btn-default pull-right" href="{{ route($moduleRouteText.'.pending') }}">
                Pending Products
            </a>
            <a class="btn btn-primary pull-right" style="margin-right: 5px;" href="{{ route('products.index') }}">
                Completed Products
            </a>
            <div class="clearfix"></div>    
            <div class="clearfix">&nbsp;</div>    
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
                                <th width="8%">ID</th>                                   
                                <th width="20%">GR ID</th>
                                <th width="30%">Title</th>
                                <th width="20%">Source</th>                           
                                <th width="10%">Created At</th>                           
                                <th width="12%">Action</th>
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
                    data.complete_product = 1;
                    data.search_title = $("#search-frm input[name='search_title']").val();
                    data.search_gr_id = $("#search-frm input[name='search_gr_id']").val();
                    data.search_source = $("#search-frm select[name='search_source']").val();
                }
            },
            "order": [[0, "desc"]],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'product_id', name: '{{ TBL_DEALER_PRODUCTS }}.product_id'},
                {data: 'title', name: '{{ TBL_DEALER_PRODUCTS }}.title'},
                {data: 'source_title', name: '{{ TBL_DEAL_SOURCE }}.title'},
                {data: 'created_at', name: '{{ TBL_DEALER_PRODUCTS }}.created_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@endsection