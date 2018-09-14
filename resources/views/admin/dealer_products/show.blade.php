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
                                            {{ date("j M, Y h:i:s A",strtotime($dealerProduct->created_at)) }}                                       
                                    </td>
                                </tr>
<!--                                 <tr>
                                    <td width="30%"><b>Category: </b></td>
                                    <td width="70%">{{ ($dealerProduct->category) }}</td>
                                </tr>                                                     
 -->                             <tr>
                                    <td width="30%"><b>Title: </b></td>
                                    <td width="70%">
                                        <a title="View Dealer Product" href="{{ $dealerProduct->link }}" target="_blank">
                                            {{ $dealerProduct->title }}
                                        </a>
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>Scrap Source: </b></td>
                                    <td width="70%">
                                            {{ $dealerProduct->source_title }}
                                    </td>
                                </tr>
                                @if(!empty($dealerProduct->description))                                                         
                                <tr>
                                    <td width="30%"><b>Description: </b></td>
                                    <td width="70%">
                                        {!! $dealerProduct->description !!}
                                    </td>
                                </tr>
                                @endif                                                                
                                <tr>
                                    <td width="30%"><b>GR ID: </b></td>
                                    <td width="70%">{{ ($dealerProduct->product_id) }}</td>
                                </tr>    

                                @if(!empty($dealerProduct->breadcrumbs))
                                <tr>
                                    <td width="30%">Breadcrumbs</td>
                                    <td width="70%">
                                        <?php 
                                            $breadcrumbs = json_decode($dealerProduct->breadcrumbs,1);
                                            $breadcrumbs = implode(" >> ", $breadcrumbs);
                                            echo $breadcrumbs;
                                        ?>
                                    </td>
                                </tr>
                                @endif
                                                

                                                                                 
<!--                                 <tr>
                                    <td width="30%"><b>Item #:</b></td>
                                    <td width="70%">{{ $dealerProduct->item_unique_id }}</td>
                                </tr>
 -->                                
                                <tr>
                                    <td width="30%"><b>UPC:</b></td>
                                    <td width="70%">{{ $dealerProduct->upc_number }}</td>
                                </tr>
                                @if(!empty($dealerProduct->mpn))
                                <tr>
                                    <td width="30%"><b>MPN:</b></td>
                                    <td width="70%">{{ $dealerProduct->mpn }}</td>
                                </tr>
                                @endif
                                @if(!empty($dealerProduct->mfg_name))
                                <tr>
                                    <td width="30%"><b>MFG Name:</b></td>
                                    <td width="70%">{{ $dealerProduct->mfg_name }}</td>
                                </tr>
                                @endif
                                @if($dealerProduct->base_price > 0)
                                <tr>
                                    <td width="30%"><b>Base Price: </b></td>
                                    <td width="70%">{{ $dealerProduct->base_price }}</td>
                                </tr>
                                @endif                                
                                @if($dealerProduct->sale_price > 0)
                                <tr>
                                    <td width="30%"><b>Sale Price: </b></td>
                                    <td width="70%">{{ $dealerProduct->sale_price }}</td>
                                </tr>
                                @endif                    
                                <tr>
                                    <td width="30%"><b>MSRP:</b></td>
                                    <td width="70%">{{ $dealerProduct->msrp }}</td>
                                </tr>                                                                
                                <tr>
                                    <td width="30%"><b>Brand:</b></td>
                                    <td width="70%">{{ $dealerProduct->brand }}</td>
                                </tr>                                
                                <tr>
                                    <td width="30%"><b>Model:</b></td>
                                    <td width="70%">{{ $dealerProduct->model }}</td>
                                </tr>               
                                @if(!empty($dealerProduct->vendor_image))                 
                                <tr>
                                    <td width="30%"><b>Vendor Image:</b></td>
                                    <td width="70%">
                                        <img src="{{ $dealerProduct->vendor_image }}" width="150" />
                                    </td>
                                </tr>                                
                                @endif
                            </table>

                            @php
                                $attrs = $dealerProduct->dealerProductAttribute ? $dealerProduct->dealerProductAttribute->toArray():[];
                                $displayKeys = ["sku","mpn","manufacturer","manufacturer part number","model"];
                            @endphp

                            @if(count($attrs))
                            <h4><b>Product Attributes</b></h4>
                            <table class="table table-bordered">
                                @foreach($attrs as $row)
                                @if(true)
                                    <tr>
                                        <td width="30%"><b>{{ $row['keyname'] }}</b></td>
                                        <td width="70%">{{ $row['keyvalue'] }}</td>
                                    </tr>                                
                                @elseif(in_array(trim(strtolower($row['keyname'])),$displayKeys))
                                    <tr>
                                        <td width="30%"><b>{{ $row['keyname'] }}</b></td>
                                        <td width="70%">{{ $row['keyvalue'] }}</td>
                                    </tr>
                                @endif
                                @endforeach                                
                            </table>
                            @endif

                            @php
                                $productPrices = $dealerProduct->dealerProductPrice ? $dealerProduct->dealerProductPrice->toArray():[];
                            @endphp         

                            @if(count($productPrices))
                            <h4><b>Product Prices</b></h4>
                            <table class="table table-bordered">
                                <tr>
                                    <th width="25%">Date</th>
                                    <th width="25%">Sale Price</th>
                                    <th width="25%">Base Price</th>
                                    <th width="25%">Quantity</th>
                                </tr>
                                @foreach($productPrices as $row)
                                    <tr>
                                        <td>{{ date("j M, Y",strtotime($row['date'])) }}</td>
                                        <td>{{ $row['sale_price'] }}</td>
                                        <td>{{ $row['base_price'] }}</td>
                                        <td>{{ $row['qty'] }}</td>
                                    </tr>
                                @endforeach
                            </table>
                            @endif                           

                            @php
                                $productPhotos = $dealerProduct->dealerProductPhoto ? $dealerProduct->dealerProductPhoto->toArray():[];
                            @endphp         
                            @if(count($productPhotos))
                            <h4><b>Photos</b></h4>
                            <table class="table table-bordered">
                                <div class="row">
                                @foreach($productPhotos as $row)
                                    <div class="col-md-3" style="margin-bottom: 10px;">
                                        <img src="{{ $row['image_url'] }}" class="img-responsive img-thumbnail" />
                                    </div>                                            
                                @endforeach
                                </div>
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
 

