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
                                    <td width="30%"><b>Category: </b></td>
                                    <td width="70%">{{ ($deal->product_category->title) }}</td>
                                </tr>                                                     
                                <tr>
                                    <td width="30%"><b>Title: </b></td>
                                    <td width="70%">
                                        <a title="View Deal" href="{{ $deal->link }}" target="_blank">
                                            {{ $deal->title }}
                                        </a>
                                    </td>
                                </tr>       
                                @if(!empty($deal->description))                                                         
                                <tr>
                                    <td width="30%"><b>Description: </b></td>
                                    <td width="70%">
                                        {!! $deal->description !!}
                                    </td>
                                </tr>
                                @endif                                                                
                                <tr>
                                    <td width="30%"><b>GR ID: </b></td>
                                    <td width="70%">{{ ($deal->product_id) }}</td>
                                </tr>                                                     
                                <tr>
                                    <td width="30%"><b>MPN #:</b></td>
                                    <td width="70%">{{ $deal->item_unique_id }}</td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>UPC:</b></td>
                                    <td width="70%">{{ $deal->upc_number }}</td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>MSRP:</b></td>
                                    <td width="70%">
                                        <?php 
                                            $priceObj = \DB::table(TBL_AMMO_PRODUCT_PRICES)
                                                        ->where("parent_id","=",$deal->id)
                                                        ->orderBy("id","desc")
                                                        ->first();

                                            if($priceObj)
                                            {
                                                echo "<b>Regular Price: </b>".$priceObj->regular_price;
                                                if(!empty($priceObj->sale_price))
                                                {
                                                    echo "<br />";
                                                    echo "<b>Sale Price: </b>".$priceObj->sale_price;
                                                }
                                            }                                                        
                                        ?>
                                    </td>
                                </tr>                                
                            </table>

                            @php
                                $attrs = [];
                                $attrsDB = $deal->attr;
                                if(!empty($attrsDB))
                                    $attrs = json_decode($attrsDB,1);


                                $keyArray = ["Manufacturer","Bullet Weight","Bullet Type","Use Type","Ammo Casing","Quantity","Ammo Caliber","Primer Type","Attracts Magnet","Quantity","Muzzle Velocity (fps)","Muzzle Energy (ft lbs)"];    

                            @endphp
                            
                            @if(count($attrs))
                            <h4><b>Product Attributes</b></h4>
                            <table class="table table-bordered">
                                @foreach($attrs as $row)
                                    @if(in_array($row['key'],$keyArray))
                                    <tr>
                                        <td width="30%"><b>{{ $row['key'] }}</b></td>
                                        <td width="70%">{{ $row['value'] }}</td>
                                    </tr>
                                    @endif                                
                                @endforeach                                
                            </table>                            
                            @endif

                            <?php 
                                $dealPhotos = json_decode($deal->images,1);
                            ?>

                            @if($dealPhotos && count($dealPhotos))
                            <h4><b>Photos</b></h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td>                                        
                                        <div class="row">
                                            @foreach($dealPhotos as $image_url)
                                            <div class="col-md-3" style="margin-bottom: 10px;">
                                                <img src="{{ $image_url }}" class="img-responsive" />
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
    });
</script>
@endsection


