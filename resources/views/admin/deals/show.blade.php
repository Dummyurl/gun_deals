@extends('admin.layouts.app')

@section('breadcrumb')

@stop

@section('content')

<div class="page-content">
    <div class="container">
        <div class="row autoResizeHeight">
            <div class="col-md-12">
                <div class="portlet box green">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-list"></i>
                            {{ $page_title }}
                        </div>
                        <a class="btn btn-default pull-right btn-sm mTop5" href="{{ $list_url }}">Back</a>
                    </div>
                    <div class="portlet-body">
                        <div class="form-body">
                            <h4><b>Basic Details</b></h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td width="30%"><b>Created At: </b></td>
                                    <td width="70%">
                                            {{ date("j M, Y h:i:s A",strtotime($deal->created_at)) }}                                       
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>Title: </b></td>
                                    <td width="70%">
                                        <a title="View Deal" href="{{ $deal->link }}" target="_blank">
                                            {{ $deal->title }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>Source: </b></td>
                                    <td width="70%">{{ ($deal->scrapSource) ? $deal->scrapSource->title:"" }}</td>
                                </tr>                                
                                <tr>
                                    <td width="30%"><b>In Stock: </b></td>
                                    <td width="70%">
                                        @if($deal->out_of_stock)
                                        <a class="btn btn-danger btn-xs">No</a>
                                        @else
                                        <a class="btn btn-success btn-xs">Yes</a>
                                        @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>Reviews: </b></td>
                                    <td width="70%">{{ ($deal->reviews_count > 0) ? $deal->reviews_count:"-"}}</td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>Ratings: </b></td>
                                    <td width="70%">{{ ($deal->ratings > 0) ? $deal->ratings."/ 5":"-"}}</td>
                                </tr>
                                @if($deal->base_price > 0)
                                <tr>
                                    <td width="30%"><b>Base Price: </b></td>
                                    <td width="70%">{{ $deal->base_price }}</td>
                                </tr>
                                @endif
                                
                                <tr>
                                    <td width="30%"><b>Sale Price: </b></td>
                                    <td width="70%">{{ $deal->sale_price }}</td>
                                </tr>
                                
                            </table>
                            
                            @if($deal->dealSpecifications && count($deal->dealSpecifications))
                            <h4><b>Deal Specifications</b></h4>
                            <table class="table table-bordered">
                                @foreach($deal->dealSpecifications as $row)
                                    <tr>
                                        <td width="30%"><b>{{ $row->key }}: </b></td>
                                        <td width="70%">{{ $row->value }}</td>
                                    </tr>                                
                                @endforeach                                
                            </table>                            
                            @endif
                            
                            @if(!empty($deal->description))
                            <h4><b>Product Description</b></h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td>                                        
                                        {!! $deal->description !!}
                                    </td>
                                </tr>
                            </table>
                            @endif

                            @if($deal->dealPhotos && count($deal->dealPhotos))
                            <h4><b>Photos</b></h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td>                                        
                                        <div class="row">
                                            @foreach($deal->dealPhotos as $row)
                                            <div class="col-md-3" style="margin-bottom: 10px;">
                                                <img src="{{ $row->image_url }}" class="img-responsive" />
                                            </div>
                                            @endforeach
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            @endif
                        </div>
                    </div>
                </div>                 
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script type="text/javascript">
    $(document).ready(function () {
        $('#main-frm').submit(function () {

            if ($(this).parsley('isValid'))
            {
                $('#AjaxLoaderDiv').fadeIn('slow');
                $.ajax({
                    type: "POST",
                    url: $(this).attr("action"),
                    data: new FormData(this),
                    contentType: false,
                    processData: false,
                    enctype: 'multipart/form-data',
                    success: function (result)
                    {
                        $('#AjaxLoaderDiv').fadeOut('slow');
                        if (result.status == 1)
                        {
                            $.bootstrapGrowl(result.msg, {type: 'success', delay: 4000});
                            window.location = '{{ $list_url }}';
                        } else
                        {
                            $.bootstrapGrowl(result.msg, {type: 'danger', delay: 4000});
                        }
                    },
                    error: function (error) {
                        $('#AjaxLoaderDiv').fadeOut('slow');
                        $.bootstrapGrowl("Internal server error !", {type: 'danger', delay: 4000});
                    }
                });
            }

            return false;
        });
    });
</script>
@endsection

