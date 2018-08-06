@extends('admin.layouts.app')



@section('content')

<!-- BEGIN PAGE CONTENT BODY -->
<div class="page-content">
    <div class="container">

        <div class="col-md-12">
            @include("admin.cities.search")

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
                               <th width="10%">ID</th>
                               <th width="20%">Country</th>                               
                               <th width="20%">State</th>
                               <th width="20%">City</th>
                               <th width="20%">Created At</th>                           
                               <th width="10%" data-orderable="false">Action</th>
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
    

    $(document).ready(function(){

            $('.coutries').on("change",function() {
                var country_id = $(this).val();

                $.ajax({
                    type: "POST",
                    url: "{{route('getstates')}}",
                   data: {
                        "_token": "{{ csrf_token() }}",
                        "country_id" : country_id,
                    },
                    success: function(data) {
                        $(".all_state").html('')
                        $(".all_state").html(data);
                    }
                });
            });


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
                    data.search_id = $("#search-frm input[name='search_id']").val();
                    data.search_start_date = $("#search-frm input[name='search_start_date']").val();
                    data.search_end_date = $("#search-frm input[name='search_end_date']").val();
                    data.search_city = $("#search-frm input[name='search_city']").val();
                    data.search_state = $("#search-frm select[name='search_state']").val();
                    data.search_country = $("#search-frm select[name='search_country']").val();
                    
                }
            },            
            "order": [[ 0, "desc" ]],    
            columns: [
                { data: 'id', name: 'id'}, 
                { data: 'country', name: '{{TBL_COUNTRY}}.title'},
                { data: 'state', name: '{{TBL_STATE}}.title'},
                { data: 'title', name: '{{TBL_CITY}}.title'},    
                { data: 'created_at', name: '{{TBL_CITY}}.created_at'},                       
                { data: 'action', orderable: false, searchable: false}             
            ]
        });        

    });

    </script>

@endsection




