<!-- BEGIN HEADER -->
<div class="page-header">
    <!-- BEGIN HEADER TOP -->
    <div class="page-header-top">
        <div class="container">
            <!-- BEGIN LOGO -->
            <div class="page-logo">
                <a href="{{ route('admin_dashboard') }}">
                    <img src="{{ asset("images/logo.png")}}" alt="logo" class="logo-default" style="max-width: 200px;margin-top: 15px !important;">
                </a>
            </div>
            <!-- END LOGO -->
            <!-- BEGIN RESPONSIVE MENU TOGGLER -->
            <a href="javascript:;" class="menu-toggler"></a>
            <!-- END RESPONSIVE MENU TOGGLER -->
            <!-- BEGIN TOP NAVIGATION MENU -->
            <div class="top-menu">
                <ul class="nav navbar-nav pull-right">

                    <!-- BEGIN USER LOGIN DROPDOWN -->
                    <li class="dropdown dropdown-user dropdown-dark">
                        <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-hover="dropdown" data-close-others="true">
                            <img alt="" class="img-circle" src="{{ asset("images/default-user.jpg")}}" />
                            <span class="username username-hide-mobile">
                                @if(Auth::guard('admins')->check())                                
                                    {{ Auth::guard('admins')->user()->name }}
                                @endif
                            </span>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-default">
                            <li>
                                <a href="{{ route('admin_edit_profile')}}">
                                    <i class="icon-user"></i> My Profile </a>
                            </li>                                    
                            <li>
                                <a href="{{ route('admin_change_password')}}">
                                    <i class="icon-key"></i> Change Password </a>
                            </li>
                            <li>
                                <a href="{{ route('admin_logout')}}">
                                    <i class="icon-logout"></i> Log Out </a>
                            </li>
                        </ul>
                    </li>
                    <!-- END USER LOGIN DROPDOWN -->
                    <!-- BEGIN QUICK SIDEBAR TOGGLER -->
                    <li class="dropdown dropdown-extended quick-sidebar-toggler">
                        <span class="sr-only">Toggle Quick Sidebar</span>
                        <i class="icon-logout" onclick="window.location = '{{ route("admin_logout") }}'"></i>

                    </li>
                    <!-- END QUICK SIDEBAR TOGGLER -->
                </ul>
            </div>
            <!-- END TOP NAVIGATION MENU -->
        </div>
    </div>
    <!-- END HEADER TOP -->
    <!-- BEGIN HEADER MENU -->
    <div class="page-header-menu">
        <div class="container">


            <!-- BEGIN MEGA MENU -->

            <div class="hor-menu  ">
                <ul class="nav navbar-nav">
                    <li class="{{ Request::is('admin','admin/dashboard') ? 'active' : '' }}">
                        <a href="{{ route('admin_dashboard')}}"> 
                            Dashboard
                        </a>
                    </li>
                    <?php 
                        $rowarray = session("admin_user_rights");
                        
                        $closeflag  = true;
                        $groupname  = "";
                        $scriptdata = '<li class="menu-dropdown classic-menu-dropdown">';
                        $groupwidth = 0;

                        foreach ($rowarray as $row)
                        {
                            if($groupname != $row["trngrouptitle"])
                            {
                                if ($groupname == "") 
                                {
                                    $scriptdata = $scriptdata.'<a href="javascript:;">'.trim($row["trngrouptitle"]).'<span class="arrow"></span></a>';

                                    $scriptdata = $scriptdata . '<ul class="dropdown-menu pull-left">';
                                    $closeflag = false;
                                } 
                                else 
                                {
                                    $scriptdata = $scriptdata . "</ul></li>";

                                    $scriptdata = $scriptdata .'<li class="menu-dropdown classic-menu-dropdown">';
                                    $scriptdata = $scriptdata.'<a href="javascript:;">'.trim($row["trngrouptitle"]).'<span class="arrow"></span></a>';

                                    $scriptdata = $scriptdata . '<ul class="dropdown-menu pull-left">';
                                    $closeflag = false;
                                }
                                
                                if($row["insubmenu"] == "Y" && $row["show_in_menu"] == "Y")
                                {
                                    $scriptdata = $scriptdata . "<li><a class='nav-link' href=\"". url($row["pageurl"])."\">".trim($row["trnname"])."</a></li>";
                                }

                                $groupname  = $row["trngrouptitle"];
                            }
                            else
                            {
                                if($row["insubmenu"] == "Y" && $row["show_in_menu"] == "Y")
                                {
                                    $scriptdata = $scriptdata . "<li><a class='nav-link' href=\"".url($row["pageurl"])."\">".trim($row["trnname"])."</a></li>";
                                }
                            }

                        }    
                        if ($closeflag == false) $scriptdata = $scriptdata . "</li></ul>";

                        $scriptdata = $scriptdata . " </li>";

                        echo $scriptdata;
                    ?>

                    <?php /*
                    <li class="menu-dropdown classic-menu-dropdown">
                        <a href="javascript:;">Users
                            <span class="arrow"></span>
                        </a>
                        <ul class="dropdown-menu pull-left">
                            <li class="">
                                <a href="{{ url("admin/users") }}" class="nav-link">Frontend Users </a>
                            </li>
                        </ul>
                    </li>                     
                    <li class="menu-dropdown classic-menu-dropdown">
                        <a href="javascript:;"> Website Modules
                            <span class="arrow"></span>
                        </a>
                        <ul class="dropdown-menu pull-left">
                            <li class=" ">
                                <a href="#" class="nav-link  "> Page 1 </a>
                            </li>
                            <li class=" ">
                                <a href="#" class="nav-link  "> Page 2 </a>
                            </li>
                            <li class=" ">
                                <a href="#" class="nav-link  "> Page 3 </a>
                            </li>                                    
                        </ul>
                    </li>
                    <li class="">
                        <a href="#"> 
                            Setting
                        </a>
                    </li>   */ ?>                 
            </div>
            <!-- END MEGA MENU -->
        </div>
    </div>
    <!-- END HEADER MENU -->
</div>

<div id="AjaxLoaderDiv" style="display: none;z-index:99999 !important;">
    <div style="width:100%; height:100%; left:0px; top:0px; position:fixed; opacity:0; filter:alpha(opacity=40); background:#000000;z-index:999999999;">
    </div>
    <div style="float:left;width:100%; left:0px; top:50%; text-align:center; position:fixed; padding:0px; z-index:999999999;">
        <img src="{{ asset('images/ajax-loader.gif') }}">
        </center>
    </div>
</div>
