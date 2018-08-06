<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <meta name="title" content="Admin Panel: {{ env('APP_SITE_TITLE')}}" />
        <title>Admin Panel: {{ isset($page_title) ? $page_title:env('APP_SITE_TITLE') }}</title>
        <!-- BEGIN GLOBAL MANDATORY STYLES -->
        <link href="http://fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/font-awesome/css/font-awesome.min.css" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/simple-line-icons/simple-line-icons.min.css" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet" type="text/css" />

        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/bootstrap-switch/css/bootstrap-switch.min.css" rel="stylesheet" type="text/css" />
        <!-- END GLOBAL MANDATORY STYLES -->
        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/bootstrap-daterangepicker/daterangepicker.min.css" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/morris/morris.css" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/fullcalendar/fullcalendar.min.css" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/jqvmap.css" rel="stylesheet" type="text/css" />
        <!-- END PAGE LEVEL PLUGINS -->
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/datatables/datatables.min.css" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.css" rel="stylesheet" type="text/css" />            
        <!-- BEGIN THEME GLOBAL STYLES -->
        <link href="{{ asset("themes/admin/assets/")}}/global/css/components.min.css" rel="stylesheet" id="style_components" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/global/css/plugins.min.css" rel="stylesheet" type="text/css" />
        <!-- END THEME GLOBAL STYLES -->
        <!-- BEGIN THEME LAYOUT STYLES -->
        <link href="{{ asset("themes/admin/assets/")}}/layouts/layout3/css/layout.min.css" rel="stylesheet" type="text/css" />
        <link href="{{ asset("themes/admin/assets/")}}/layouts/layout3/css/themes/default.min.css" rel="stylesheet" type="text/css" id="style_color" />
        <link href="{{ asset("themes/admin/assets/")}}/layouts/layout3/css/custom.min.css" rel="stylesheet" type="text/css" />
        <!-- END THEME LAYOUT STYLES -->
        
        <link rel="stylesheet" type="text/css" media="screen" href="{{ asset('css/target-admin2.css') }}" />                
        <link rel="stylesheet" href="{{ asset('css/jquery-ui.css') }}" />
            <style>
                .mTop25{margin-top: 25px;}
                .mTop5{margin-top: 5px;}
                .pagination>.active>span{background-color: #32c5d2 !important;border-color: #32c5d2 !important}
                .dataTables_paginate .fa
                {
                    font-size: 14px !important;
                    padding: 2.5px !important;
                }

            </style>
            @yield('styles')        
    </head>
    <body class="page-container-bg-solid page-boxed">
        @include('admin.includes.header')   

        <!-- BEGIN CONTAINER -->
        <div class="page-container">
            <!-- BEGIN CONTENT -->
            <div class="page-content-wrapper">
                <!-- BEGIN CONTENT BODY -->
                @include('admin.includes.flashMsg')

                @yield('content')           

                <!-- BEGIN PAGE CONTENT BODY -->        
            </div>
        </div>        


        @include('admin.includes.footer')                     

        {!! Form::open(['method' => 'DELETE','id' => 'global_delete_form']) !!}
        {!! Form::hidden('id', 0,['id' => 'delete_id']) !!}
        {!! Form::close() !!}            


        <!--[if lt IE 9]>
       <script src="{{ asset("themes/admin/assets/")}}/global/plugins/respond.min.js"></script>
       <script src="{{ asset("themes/admin/assets/")}}/global/plugins/excanvas.min.js"></script> 
       <![endif]-->
        <!-- BEGIN CORE PLUGINS -->
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jquery.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/bootstrap/js/bootstrap.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/js.cookie.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jquery-slimscroll/jquery.slimscroll.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jquery.blockui.min.js" type="text/javascript"></script>
        
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/bootstrap-switch/js/bootstrap-switch.min.js" type="text/javascript"></script>
        <!-- END CORE PLUGINS -->
        <script src="{{ asset("themes/admin/assets/")}}/global/scripts/datatable.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/datatables/datatables.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/datatables/plugins/bootstrap/datatables.bootstrap.js" type="text/javascript"></script>            
        <!-- BEGIN PAGE LEVEL PLUGINS -->
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/moment.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/bootstrap-daterangepicker/daterangepicker.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/morris/morris.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/morris/raphael-min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/counterup/jquery.waypoints.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/counterup/jquery.counterup.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amcharts/amcharts.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amcharts/serial.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amcharts/pie.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amcharts/radar.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amcharts/themes/light.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amcharts/themes/patterns.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amcharts/themes/chalk.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/ammap/ammap.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/ammap/maps/js/worldLow.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/amcharts/amstockcharts/amstock.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/fullcalendar/fullcalendar.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/flot/jquery.flot.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/flot/jquery.flot.resize.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/flot/jquery.flot.categories.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jquery-easypiechart/jquery.easypiechart.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jquery.sparkline.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/jquery.vmap.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.russia.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.world.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.europe.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.germany.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/maps/jquery.vmap.usa.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/global/plugins/jqvmap/jqvmap/data/jquery.vmap.sampledata.js" type="text/javascript"></script>
        <!-- END PAGE LEVEL PLUGINS -->
        <!-- BEGIN THEME GLOBAL SCRIPTS -->
        <script src="{{ asset("themes/admin/assets/")}}/global/scripts/app.min.js" type="text/javascript"></script>
        <!-- END THEME GLOBAL SCRIPTS -->
        <!-- BEGIN PAGE LEVEL SCRIPTS -->
        <script src="{{ asset("themes/admin/assets/")}}/pages/scripts/dashboard.min.js" type="text/javascript"></script>
        <!-- END PAGE LEVEL SCRIPTS -->
        <!-- BEGIN THEME LAYOUT SCRIPTS -->
        <script src="{{ asset("themes/admin/assets/")}}/layouts/layout3/scripts/layout.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/layouts/layout3/scripts/demo.min.js" type="text/javascript"></script>
        <script src="{{ asset("themes/admin/assets/")}}/layouts/global/scripts/quick-sidebar.min.js" type="text/javascript"></script>        
        <script type="text/javascript" src="{{ asset('js/parsley.js') }}"></script>
        <script type="text/javascript" src="{{ asset('thirdparty/tinymce/tinymce.min.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/comman.js') }}"></script>
        <script type="text/javascript" src="{{ asset('js/jquery.bootstrap-growl.min.js') }}"></script>
        <script src="{{ asset('js/jquery-ui.js') }}"></script>
        <script src="{{ asset('thirdparty/fancybox/jquery.fancybox.pack.js') }}" type="text/javascript"></script>
        <link href="{{ asset('thirdparty/fancybox/jquery.fancybox.css') }}" rel="stylesheet">
    <script type="text/javascript">
    $(document).ready(function () {

            $("#record_per_page_select").change(function(){
               $("#record_per_page").val($(this).val());
               $("#search-frm").submit();
            });

            $('.fancybox_iframe').fancybox({
                'type': 'iframe',
            });

            $('.fancybox_iframe_full').fancybox({
                'type': 'iframe',
                'width': '90%',
                'height': '90%',
            });

            $('.fancybox').fancybox();

            $(document).on('click', '#chk_all', function () {
                if ($(this).is(':checked'))
                {
                    $('.chkids').prop('checked', true);
                }
                else
                {
                    $('.chkids').prop('checked', false);
                }
            });

            $('.dropdown').find('a').click(function () {
                $(this).parent().toggleClass('open');
            });

            $(document).on('click', '.btn-delete-record', function () {

                $text = 'Are you sure ?';

                if ($(this).attr('title') == "delete user")
                {
                    $text = 'Are you sure you want to delete this user ?';
                }

                if (confirm($text))
                {
                    $url = $(this).attr('href');
                    $('#global_delete_form').attr('action', $url);
                    $('#global_delete_form #delete_id').val($(this).data('id'));
                    $('#global_delete_form').submit();
                }

                return false;
            });

            $("#start_date").datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                yearRange: '1900:2050',
                showButtonPanel: false,
                onClose: function (selectedDate) {
                    $("#end_date").datepicker("option", "minDate", selectedDate);
                }
            });

            $("#end_date").datepicker({
                dateFormat: 'yy-mm-dd',
                changeYear: true,
                changeMonth: true,
                yearRange: '1900:2050',
                showButtonPanel: false,
                onClose: function (selectedDate) {
                    $("#start_date").datepicker("option", "maxDate", selectedDate);
                }
            });

    });
    </script>        
    @yield('scripts')
    </body>    
</html>
