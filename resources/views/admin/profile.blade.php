@extends('admin.layouts.app')

@section('breadcrumb')

<?php
$pageTitle = "Change your password";

$bred_crumb_array = array(
    'Home' => url('admin'),
    'Change your password' => '',
);
?>



@stop

@section('content')

<div class="page-content">
    <div class="container">
        <div class="row">
            <div class="col-md-6 col-md-offset-3">
                <div class="portlet box green">
                    <div class="portlet-title">
                        <div class="caption">
                            <i class="fa fa-key"></i>
                            Edit Your Profile
                        </div>
                    </div>
                    <div class="portlet-body">
                        {!! Form::model(Auth::guard("admins")->user(), ['route' => 'admin_update_profile', 'class' => 'form', 'id' => 'main-frm']) !!}
                            
                            <div class="form-body">
                                <div class="form-group">
                                    <label class="control-label">Name</label>                                    
                                    {!! Form::text('name',null,['placeholder' => 'Enter Your Name','data-required' => true, 'class' => "form-control"]) !!}
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Email</label>                                    
                                    {!! Form::text('email',null,['placeholder' => 'Enter Your Email','data-required' => true, 'data-type' => "email",'class' => "form-control"]) !!}
                                </div>
                                <div class="form-group">
                                    <label class="control-label">Phone</label>                                    
                                    {!! Form::text('phone',null,['placeholder' => 'Enter Your Phone','data-required' => true, 'class' => "form-control"]) !!}
                                </div>
                                <div class="form-group">
                                    <input type="submit" value="Update" class="btn btn-success pull-right"/>
                                </div>
                                <div class="clearfix">&nbsp;</div>
                            </div>
                        </form>    
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
    });
</script>
@endsection


