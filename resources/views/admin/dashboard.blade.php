@extends('admin.layouts.app')

<?php
$pageTitle = "Dashboard";

$bred_crumb_array = array(
    'Home' => url('backend'),
    'Dashboard' => '',
);
?>
@section('content')
<div class="page-head">
    <div class="container">
        <!-- BEGIN PAGE TITLE -->
        <div class="page-title">
            <h1>Dashboard 
                <small>dashboard & statistics</small>
            </h1>
        </div>

    </div>
</div>                    
<!-- BEGIN PAGE CONTENT BODY -->
<div class="page-content">
    <div class="container">

        <div class="page-content-inner">
            <div class="row widget-row">
                <div class="col-md-3">
                    <!-- BEGIN WIDGET THUMB -->
                    <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 ">
                        <h4 class="widget-thumb-heading">Current Balance</h4>
                        <div class="widget-thumb-wrap">
                            <i class="widget-thumb-icon bg-green icon-bulb"></i>
                            <div class="widget-thumb-body">
                                <span class="widget-thumb-subtitle">USD</span>
                                <span class="widget-thumb-body-stat" data-counter="counterup" data-value="7,644">0</span>
                            </div>
                        </div>
                    </div>
                    <!-- END WIDGET THUMB -->
                </div>
                <div class="col-md-3">
                    <!-- BEGIN WIDGET THUMB -->
                    <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 ">
                        <h4 class="widget-thumb-heading">Weekly Sales</h4>
                        <div class="widget-thumb-wrap">
                            <i class="widget-thumb-icon bg-red icon-layers"></i>
                            <div class="widget-thumb-body">
                                <span class="widget-thumb-subtitle">USD</span>
                                <span class="widget-thumb-body-stat" data-counter="counterup" data-value="1,293">0</span>
                            </div>
                        </div>
                    </div>
                    <!-- END WIDGET THUMB -->
                </div>
                <div class="col-md-3">
                    <!-- BEGIN WIDGET THUMB -->
                    <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 ">
                        <h4 class="widget-thumb-heading">Biggest Purchase</h4>
                        <div class="widget-thumb-wrap">
                            <i class="widget-thumb-icon bg-purple icon-screen-desktop"></i>
                            <div class="widget-thumb-body">
                                <span class="widget-thumb-subtitle">USD</span>
                                <span class="widget-thumb-body-stat" data-counter="counterup" data-value="815">0</span>
                            </div>
                        </div>
                    </div>
                    <!-- END WIDGET THUMB -->
                </div>
                <div class="col-md-3">
                    <!-- BEGIN WIDGET THUMB -->
                    <div class="widget-thumb widget-bg-color-white text-uppercase margin-bottom-20 ">
                        <h4 class="widget-thumb-heading">Average Monthly</h4>
                        <div class="widget-thumb-wrap">
                            <i class="widget-thumb-icon bg-blue icon-bar-chart"></i>
                            <div class="widget-thumb-body">
                                <span class="widget-thumb-subtitle">USD</span>
                                <span class="widget-thumb-body-stat" data-counter="counterup" data-value="5,071">0</span>
                            </div>
                        </div>
                    </div>
                    <!-- END WIDGET THUMB -->
                </div>
            </div>

            <div class="row">
                <div class="col-md-6 col-sm-6">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="icon-cursor font-purple"></i>
                                <span class="caption-subject font-purple bold uppercase">General Stats</span>
                            </div>
                            <div class="actions">
                                <a href="javascript:;" class="btn btn-sm btn-circle red easy-pie-chart-reload">
                                    <i class="fa fa-repeat"></i> Reload </a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="easy-pie-chart">
                                        <div class="number transactions" data-percent="55">
                                            <span>+55</span>% </div>
                                        <a class="title" href="javascript:;"> Transactions
                                            <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="margin-bottom-10 visible-sm"> </div>
                                <div class="col-md-4">
                                    <div class="easy-pie-chart">
                                        <div class="number visits" data-percent="85">
                                            <span>+85</span>% </div>
                                        <a class="title" href="javascript:;"> New Visits
                                            <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="margin-bottom-10 visible-sm"> </div>
                                <div class="col-md-4">
                                    <div class="easy-pie-chart">
                                        <div class="number bounce" data-percent="46">
                                            <span>-46</span>% </div>
                                        <a class="title" href="javascript:;"> Bounce
                                            <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="col-md-6 col-sm-6">
                    <div class="portlet light ">
                        <div class="portlet-title">
                            <div class="caption">
                                <i class="icon-equalizer font-yellow"></i>
                                <span class="caption-subject font-yellow bold uppercase">Server Stats</span>
                                <span class="caption-helper">monthly stats...</span>
                            </div>
                            <div class="tools">
                                <a href="" class="collapse"> </a>
                                <a href="#portlet-config" data-toggle="modal" class="config"> </a>
                                <a href="" class="reload"> </a>
                                <a href="" class="remove"> </a>
                            </div>
                        </div>
                        <div class="portlet-body">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="sparkline-chart">
                                        <div class="number" id="sparkline_bar5"></div>
                                        <a class="title" href="javascript:;"> Network
                                            <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="margin-bottom-10 visible-sm"> </div>
                                <div class="col-md-4">
                                    <div class="sparkline-chart">
                                        <div class="number" id="sparkline_bar6"></div>
                                        <a class="title" href="javascript:;"> CPU Load
                                            <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                                <div class="margin-bottom-10 visible-sm"> </div>
                                <div class="col-md-4">
                                    <div class="sparkline-chart">
                                        <div class="number" id="sparkline_line"></div>
                                        <a class="title" href="javascript:;"> Load Rate
                                            <i class="icon-arrow-right"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>


    </div>
</div>            
@endsection


@section('scripts')

@endsection



