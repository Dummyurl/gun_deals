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
                                            {{ !empty($deal->title) ? $deal->title:'No title- view link' }}
                                        </a>
                                    </td>
                                </tr>

                                <tr>
                                    <td width="30%"><b>GR ID: </b></td>
                                    <td width="70%">
                                        <a title="View Deal" href="{{ $deal->link }}" target="_blank">
                                            {{ $deal->unique_id }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>UPC: </b></td>
                                    <td width="70%">
                                        <a title="View Deal" href="{{ $deal->link }}" target="_blank">
                                            {{ $deal->upc_number }}
                                        </a>
                                    </td>
                                </tr>

                                @if(!empty($deal->mpn))
                                <tr>
                                    <td width="30%"><b>MPN:</b></td>
                                    <td width="70%">{{ $deal->mpn }}</td>
                                </tr>
                                @endif
                                @if(!empty($deal->mfg_name))
                                <tr>
                                    <td width="30%"><b>MFG Name:</b></td>
                                    <td width="70%">{{ $deal->mfg_name }}</td>
                                </tr>
                                @endif


<!--                                 <tr>
                                    <td width="30%"><b>Source: </b></td>
                                    <td width="70%">{{ ($deal->scrapSource) ? $deal->scrapSource->title:"" }}</td>
                                </tr>                     
 -->                                
<!--                                 <tr>
                                    <td width="30%"><b>Category: </b></td>
                                    <td width="70%">
                                        @if($deal->category_id > 0)
                                        @php
                                            $categories = getBreadCrumbArr($deal->category_id);
                                        @endphp                                        
                                        {!! implode(" >> ",$categories) !!}
                                        @else
                                            -
                                        @endif
                                    </td>
                                </tr>                                
 -->                                
                                @if(!empty($deal->breadcrumbs))
                                <tr>
                                    <td width="30%">Breadcrumbs</td>
                                    <td width="70%">
                                        <?php 
                                            $breadcrumbs = json_decode($deal->breadcrumbs,1);
                                            $breadcrumbs = implode(" >> ", $breadcrumbs);
                                            echo $breadcrumbs;
                                        ?>
                                    </td>
                                </tr>
                                @endif
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
                                
                                @if(!empty($deal->vendor_image))                 
                                <tr>
                                    <td width="30%"><b>Vendor Image:</b></td>
                                    <td width="70%">
                                        <img src="{{ $deal->vendor_image }}" width="150" />
                                    </td>
                                </tr>                                
                                @endif

                            </table>
                            
                            @php
                                $qty_options = $deal->qty_options;
                                if(!empty($qty_options))
                                {
                                    $qty_options = json_decode($qty_options,1);
                                }
                            @endphp
                            
                            @if(is_array($qty_options) && count($qty_options) > 0)
                                <h4><b>Quantity Pricing</b></h4>
                                <table class="table table-bordered">
                                    @foreach($qty_options as $r)
                                        <tr>
                                            <td width="40%"><b>{{ $r['key'] }}</b></td>
                                            <td width="60%">{{ $r['value'] }}</td>
                                        </tr>                                
                                    @endforeach                                
                                </table>                                                            
                            @endif                            
                            @if($deal->dealSpecifications && count($deal->dealSpecifications))

                            <?php 
                                $displayKeys = ["sku","mpn","manufacturer","manufacturer part number","model"];
                            ?>

                            <h4><b>Deal Specifications: {{ $deal->source_id }}</b></h4>
                            <table class="table table-bordered">
                                @foreach($deal->dealSpecifications as $row)
                                    @if($deal->source_id == 11)                        
                                    <tr>
                                        <td width="30%"><b>{{ $row->key }}: </b></td>
                                        <td width="70%">{{ $row->value }}</td>
                                    </tr>                                    
                                    @elseif(in_array(trim(strtolower($row->key)),$displayKeys))
                                    <tr>
                                        <td width="30%"><b>{{ $row->key }}: </b></td>
                                        <td width="70%">{{ $row->value }}</td>
                                    </tr>
                                    @endif                                
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


