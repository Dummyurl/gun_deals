

@extends('admin.layouts.app')

@section('breadcrumb')


@stop

@section('content')

<div class="page-content">
    <div class="container">
        <div class="row">
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
                                    <div class="col-md-6">
                                        <label class="control-label">Country:<span class="required">*</span></label>                                        
                                        {!! Form::select('country_id', [''=>'Select Country'] + $countryList, null, ['class' => 'form-control', 'data-required' => 'true','id' => 'cid']) !!} 
                                     </div>
                                    <div class="col-md-6">
                                        <label class="control-label">State:<span class="required">*</span></label>                                        
                                        {!! Form::select('state_id', [''=>'Select State'], null, ['class' => 'form-control all_state']) !!} 
                                     </div>
                                                                           
                                </div> 
                                <div class="clearfix">&nbsp;</div>
                                <div class="row ">
                                    <div class="col-md-12">
                                        <label class="control-label">Title:<span class="required">*</span></label>                                        
                                        {!! Form::text('title',null,['class' => 'form-control', 'data-required' => true]) !!}
                                    </div>
                                </div>
                                
                                                              
                                                         
                                <div class="clearfix">&nbsp;</div>
                                <div class="row">
                                    <div class="col-md-12">
                                        <input type="submit" value="{{ $buttonText}}" class="btn btn-success pull-right" />
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
<script src="{{ asset('js/jquery.bootstrap-growl.min.js') }}" type="text/javascript"></script>
<script type="text/javascript">
    var firstTime = 0;
    $(document).ready(function () {


         $('#cid').on("change",function() {

                var country_id = $(this).val();
                $('#AjaxLoaderDiv').fadeIn('slow');
               
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
                        $('#AjaxLoaderDiv').fadeOut('slow');
                        if(firstTime == 0){
                            @if(isset($def_c_id) && $def_c_id > 0)
                              setTimeout(function(){
                                $(".all_state").val({{ $def_state_id }});
                              },400)
                            @endif
                        }
                        firstTime = firstTime + 1;
                    }
                });
            });

        $('#main-frm').submit(function () {
            
            if (true)
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
                        }   
                        else
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

        @if(isset($def_c_id) && $def_c_id > 0)
            $("#cid").val({{ $def_c_id }});
            $("#cid").trigger("change");
        @endif
    });
</script>
@endsection