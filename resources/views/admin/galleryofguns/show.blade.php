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

                            @php
                                if(!empty($deal->attr))
                                $attrs = json_decode($deal->attr,1);
                                else
                                $attrs = [];

                                $upc = "";
                                $brand = "";
                                if(count($attrs) > 0)
                                {
                                    foreach($attrs as $row)
                                    {
                                        $key = $row['key'];
                                        $key = str_replace(":\u00a0","",$key);
                                        $key = str_replace(":","",$key);
                                        if($key == "UPC")
                                        {
                                            $upc = $row['val'];
                                        }
                                        else if($key == "Brand")
                                        {
                                            $brand = $row['val'];
                                        }
                                    }                                    
                                }
                            @endphp

                            <table class="table table-bordered">
                                <tr>
                                    <td width="30%"><b>Created At: </b></td>
                                    <td width="70%">
                                            {{ date("j M, Y h:i:s A",strtotime($deal->created_at)) }}                                       
                                    </td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>Category: </b></td>
                                    <td width="70%">{{ ($deal->category) }}</td>
                                </tr>                                                     
                                <tr>
                                    <td width="30%"><b>Unique ID: </b></td>
                                    <td width="70%">
                                        @php
                                            $uniqueID = "gr";

                                        @endphp
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
                                    <td width="30%"><b>Item #:</b></td>
                                    <td width="70%">{{ $deal->item_unique_id }}</td>
                                </tr>
                                <tr>
                                    <td width="30%"><b>MSRP:</b></td>
                                    <td width="70%">{{ $deal->msrp }}</td>
                                </tr>
                                
                            </table>

                            @php
                                $attrs = json_decode($deal->attr,1);
                            @endphp
                            
                            @if(count($attrs))
                            <h4><b>Product Attributes</b></h4>
                            <table class="table table-bordered">
                                @foreach($attrs as $row)
                                    <tr>
                                        <td width="30%"><b>{{ $row['key'] }}</b></td>
                                        <td width="70%">{{ $row['val'] }}</td>
                                    </tr>                                
                                @endforeach                                
                            </table>                            
                            @endif

                            
                            <h4><b>Photo (Thumb Image, Main Image)</b></h4>
                            <table class="table table-bordered">
                                <tr>
                                    <td>                                        
                                        <div class="row">
                                            <div class="col-md-4" style="margin-bottom: 10px;">
                                                <img src="{{ $deal->thumb_image }}" class="img-responsive" />
                                            </div>                                            
                                            <div class="col-md-8" style="margin-bottom: 10px;">
                                                <img src="{{ $deal->image }}" class="img-responsive" />
                                            </div>                                            
                                        </div>
                                    </td>
                                </tr>
                            </table>
                            
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


