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
                            {!! Form::model($formObj,['method' => $method,'files' => true, 'route' => [$action_url,$action_params],'class' => 'sky-form form form-group', 'id' => 'main-frm']) !!} 

                            <div class="row">
                                <div class="col-md-4">
                                    <label class="control-label">GR ID</label>
                                    {!! Form::text('product_id',null,['class' => 'form-control']) !!}
                                </div>                                
                                <div class="col-md-4">
                                    <label class="control-label">Item #</label>
                                    {!! Form::text('item_unique_id',null,['class' => 'form-control']) !!}
                                </div>                                
                                <div class="col-md-4">
                                    <label class="control-label">Title<span class="required">*</span></label>
                                    {!! Form::text('title',null,['class' => 'form-control', 'data-required' => true]) !!}
                                </div>                                
                            </div>                                  
                            <div class="clearfix">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <label class="control-label">Description</label>
                                    {!! Form::textarea('description',null,['class' => 'form-control','rows' => 3]) !!}
                                </div>                                
                            </div>                                  
                            <div class="clearfix">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="control-label">UPC<span class="required">*</span></label>
                                    {!! 
                                        Form::text('upc_number',null,['class' => 'form-control']) 
                                    !!}                                    
                                </div>
                                <div class="col-md-6">
                                    <label class="control-label">MSRP</label>
                                    {!! 
                                        Form::text('msrp',null,['class' => 'form-control']) 
                                    !!}                                    
                                </div>
                            </div>
                            <div class="clearfix">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-6">
                                    <label class="control-label">Brand</label>
                                    {!! 
                                        Form::text('brand',null,['class' => 'form-control']) 
                                    !!}                                    
                                </div>
                                <div class="col-md-6">
                                    <label class="control-label">Model</label>
                                    {!! 
                                        Form::text('model',null,['class' => 'form-control']) 
                                    !!}                                    
                                </div>
                            </div>
                            <div class="clearfix">&nbsp;</div>
                            <h4><b>Product Attributes:</b></h4>
                            @if($formObj->id > 0 && $formObj->productAttributes)
                            @else
                            <div class="row">                                
                                <div class="col-md-4">
                                    <label class="control-label">Key</label>
                                    {!! 
                                        Form::text('key[0]',null,['class' => 'form-control']) 
                                    !!}                                                                        
                                </div>
                                <div class="col-md-4">
                                    <label class="control-label">Value</label>
                                    {!! 
                                        Form::text('value[0]',null,['class' => 'form-control']) 
                                    !!}                                                                                                            
                                </div>

                                <div class="col-md-4">
                                    <a class="btn btn-sm btn-primary btn-add-key" style="margin-top: 25px;">+ Add More</a>
                                </div>
                            </div>
                            @endif
                            @php
                                $keyCounter = 0;
                            @endphp

                            <div id="product-features">
                                @if($formObj->id > 0 && $formObj->productAttributes)
                                    <div class="col-md-4 pull-right">
                                        <a class="btn btn-sm btn-primary btn-add-key" style="margin-top: 25px;">+ Add More</a>
                                    </div>
                                    <div class="clearfix"></div>                                
                                    @foreach($formObj->productAttributes as $row)
                                    <div class="row" style="margin-top: 5px;">
                                        <div class="col-md-4">
                                            <label class="control-label">Key</label>
                                            {!! 
                                                Form::text('key['.$keyCounter.']',$row->keyname,['class' => 'form-control']) 
                                            !!}                                                                        
                                        </div>
                                        <div class="col-md-4">
                                            <label class="control-label">Value</label>
                                            {!! 
                                                Form::text('value['.$keyCounter.']',$row->keyvalue,['class' => 'form-control']) 
                                            !!}                                                                                                            
                                        </div>
                                        <div class="col-md-4">
                                            <a class="btn btn-sm btn-danger btn-delete-key" style="margin-top: 25px;">- Remove</a>
                                        </div>
                                        @php
                                            $keyCounter++;
                                        @endphp
                                    </div>
                                    @endforeach
                                @endif
                            </div>
                            <div class="clearfix">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-4">
                                    <label class="control-label">Image</label>
                                    <input type="file" name="image" />
                                </div>
                                <div class="col-md-4">
                                    @if(isset($formObj->image) && !empty($formObj->image))
                                        <img src="{{ $formObj->image }}" class="img-responsive" />
                                    @endif
                                </div>
                            </div>
                            <div class="clearfix">&nbsp;</div>
                            <div class="row">
                                <div class="col-md-12">
                                    <input type="submit" value="Save" class="btn btn-success pull-right" />
                                </div>
                            </div>


                            {!! Form::close() !!}
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

        var keyCounter = {{ $keyCounter }};

        $(document).on("click",".btn-delete-key",function(){
            $(this).parent().parent().remove();
        });    

        $(document).on("click",".btn-add-key",function(){

            keyCounter++;

            $html = "";
            $html += '<div class="row" style="margin-top:5px;">';

                $html += '<div class="col-md-4">';
                    $html += '<label class="control-label">Key</label>';
                    $html += '<input type="text" class="form-control" name="key['+keyCounter+']" />';
                $html += '</div>';

                $html += '<div class="col-md-4">';
                    $html += '<label class="control-label">Value</label>';
                    $html += '<input type="text" class="form-control" name="value['+keyCounter+']" />';
                $html += '</div>';
                $html += '<div class="col-md-4">';
                $html += '<a class="btn btn-sm btn-danger btn-delete-key" style="margin-top: 25px;">- Remove</a>';
                $html += '</div>';
            $html += '</div';

            $("#product-features").append($html);
        });

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


