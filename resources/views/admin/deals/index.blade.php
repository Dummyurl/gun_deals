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
                            <tr>
                                <th width="5%">ID</th>                                   
                                <th width="30%">Title</th>
                                <th width="15%">Source</th>
                                <th width="15%">Product</th>
                                <th width="10%">Featured</th>                           
                                <th width="15%">Created At</th>                           
                                <th width="10%">Action</th>
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
                    data.search_product_id = $("#search-frm input[name='search_product_id']").val();
                    data.search_title = $("#search-frm input[name='search_title']").val();
                    data.source_id = $("#search-frm select[name='source_id']").val();
                    data.search_featured = $("#search-frm select[name='search_featured']").val();
                    if($("#onlymap_deals").is(":checked"))
                    {
                        data.onlymap_deals = 1;
                    }   
                    else
                    {
                        data.onlymap_deals = 0;
                    }
                }
            },
            "order": [[0, "desc"]],
            columns: [
                {data: 'id', name: 'id'},
                {data: 'title', name: '{{ TBL_DEALS }}.title'},
                {data: 'source_title', name: '{{ TBL_DEAL_SOURCE }}.title'},
                {data: 'product_title', name: '{{ TBL_PRODUCTS }}.title'},
                {data: 'is_featured', name: '{{ TBL_DEALS }}.is_featured'},
                {data: 'created_at', name: '{{ TBL_DEALS }}.created_at'},
                {data: 'action', orderable: false, searchable: false}
            ]
        });
    });
</script>
@endsection