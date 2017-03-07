<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" ng-app="myApp">
        <title>Allbright | Portal</title>
        <meta content='width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no' name='viewport'>
        <meta name="_token" content="{!! csrf_token() !!}"/>
        <!-- faveo favicon -->
        <link href="{{asset("lb-faveo/media/images/favicon.ico")}}" rel="shortcut icon">
        <!-- Bootstrap 3.3.2 -->
        <link href="{{asset("lb-faveo/css/bootstrap.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- Font Awesome Icons -->
        <link href="{{asset("lb-faveo/css/font-awesome.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- Ionicons -->
        <link href="{{asset("lb-faveo/css/ionicons.min.css")}}" rel="stylesheet"  type="text/css" />
        <!-- Theme style -->
        <link href="{{asset("lb-faveo/css/AdminLTE.css")}}" rel="stylesheet" type="text/css" />
        <!-- AdminLTE Skins. Choose a skin from the css/skins folder instead of downloading all of them to reduce the load. -->
        <link href="{{asset("lb-faveo/css/skins/_all-skins.min.css")}}" rel="stylesheet" type="text/css" />
        <!-- iCheck -->
        <link href="{{asset("lb-faveo/plugins/iCheck/flat/blue.css")}}" rel="stylesheet" type="text/css" />
        <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
        <link href="{{asset("lb-faveo/css/tabby.css")}}" rel="stylesheet" type="text/css"/>

        <link href="{{asset("lb-faveo/css/jquerysctipttop.css")}}" rel="stylesheet" type="text/css"/>
        <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
        <link href="{{asset("lb-faveo/css/editor.css")}}" rel="stylesheet" type="text/css"/>

        <link href="{{asset("lb-faveo/css/jquery.ui.css")}}" rel="stylesheet" rel="stylesheet"/>

        <link href="{{asset("lb-faveo/plugins/datatables/dataTables.bootstrap.css")}}" rel="stylesheet"  type="text/css"/>

        <link href="{{asset("lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.min.css")}}" rel="stylesheet" type="text/css"/>

        <link href="{{asset("lb-faveo/css/faveo-css.css")}}" rel="stylesheet" type="text/css" />

        <link href="{{asset("lb-faveo/css/notification-style.css")}}" rel="stylesheet" type="text/css" >

        <link href="{{asset("lb-faveo/css/jquery.rating.css")}}" rel="stylesheet" type="text/css" />
        <!-- Select2 -->
        <link href="{{asset("lb-faveo/plugins/select2/select2.min.css")}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("css/close-button.css")}}" rel="stylesheet" type="text/css" />

        <!--Daterangepicker-->
        <link rel="stylesheet" href="{{asset("lb-faveo/plugins/daterangepicker/daterangepicker.css")}}" rel="stylesheet" type="text/css" />
        <!--calendar -->
        <!-- fullCalendar 2.2.5-->
        <link href="{{asset('lb-faveo/plugins/fullcalendar/fullcalendar.min.css')}}" rel="stylesheet" type="text/css" />

        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.3.0/respond.min.js"></script>
        <![endif]-->
        <script src="{{asset("lb-faveo/js/jquery-2.1.4.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/js/jquery2.1.1.min.js")}}" type="text/javascript"></script>

        @yield('HeadInclude')
    </head>
    <body class="skin-blue fixed">
        <div class="wrapper">
            <header class="main-header">
                <a href="/" class="logo"><img src="{{ asset('lb-faveo/media/images/logo.png')}}" width="100px;"></a>

                <!-- Header Navbar: style can be found in header.less -->
                <nav class="navbar navbar-static-top">
                    <!-- Sidebar toggle button-->
                    <a href="#" class="sidebar-toggle" data-toggle="offcanvas" role="button">
                        <span class="sr-only">Toggle navigation</span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                        <span class="icon-bar"></span>
                    </a>
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <ul class="tabs tabs-horizontal nav navbar-nav navbar-left">
                            <li @yield('Dashboard')><a id="dash" data-target="#tabA" href="{{URL::route('home')}}" onclick="clickDashboard(event);">{!! Lang::get('lang.dashboard') !!}</a></li>
                            <li @yield('Cdn')><a data-target="#tabB" href="#">{!! Lang::get('lang.Cdn') !!}</a></li>
                            <li @yield('Tickets')><a data-target="#tabC" href="#">{!! Lang::get('lang.tickets') !!}</a></li>
                            <?php \Event::fire('calendar.topbar', array()); ?>
                        </ul>

                        <ul class="nav navbar-nav navbar-right">
                            @include('themes.default1.update.notification')
                            <!-- User Account: style can be found in dropdown.less -->
                            <li class="dropdown notifications-menu" id="myDropdown">
                                <a href="#" class="dropdown-toggle" data-toggle="dropdown" onclick="myFunction()">
                                    <i class="fa fa-bell-o"></i>
                                    <span class="label label-danger" id="count">{!! $notifications->count() !!}</span>
                                </a>
                                <ul class="dropdown-menu" style="width:500px">

                                    <div id="alert11" class="alert alert-success alert-dismissable" style="display:none;">
                                        <button id="dismiss11" type="button" class="close" data-dismiss="alert" aria-hidden="true">×</button>
                                        <h4><i class="icon fa fa-check"></i>Alert!</h4>
                                        <div id="message-success1"></div>
                                    </div>

                                    <li id="refreshNote">

                                    <li class="header">You have {!! $notifications->count() !!} notifications. <a class="pull-right" id="read-all" href="#">Mark all as read.</a></li>

                                    <ul class="menu">

                                        @if($notifications->count())
                                        @foreach($notifications->orderBy('created_at', 'desc')->get()->take(10) as $notification)

                                        @if($notification->notification->type->type == 'registration')
                                        @if($notification->is_read == 1)
                                        <li class="task" style="list-style: none; margin-left: -30px;"><span>&nbsp<img src="{{$notification -> users -> profile_pic}}" class="user-image"  style="width:6%;height: 5%" alt="User Image" />
                                                <a href="{!! route('user.show', $notification->notification->model_id) !!}" id="{{$notification -> notification_id}}" class='noti_User'>
                                                    {!! $notification->notification->type->message !!}
                                                </a></span>
                                        </li>
                                        @else
                                        <li style="list-style: none; margin-left: -30px;"><span>&nbsp<img src="{{$notification -> users -> profile_pic}}" class="user-image"  style="width:6%;height: 5%" alt="User Image" />
                                                <a href="{!! route('user.show', $notification->notification->model_id) !!}" id="{{$notification -> notification_id}}" class='noti_User'>
                                                    {!! $notification->notification->type->message !!}
                                                </a></span>
                                        </li>
                                        @endif
                                        @else
                                        @if($notification->is_read == 1)
                                        <li  class="task" style="list-style: none;margin-left: -30px"><span>&nbsp<img src="{{$notification -> users -> profile_pic}}" class="img-circle"  style="width:6%;height: 5%" alt="User Image" />
                                                <a href="{!! route('ticket.thread', $notification->notification->model_id) !!}" id='{{ $notification -> notification_id}}' class='noti_User'>
                                                    {!! $notification->notification->type->message !!} with id "{!!$notification->notification->model->ticket_number!!}"
                                                </a></span>
                                        </li>
                                        @elseif($notification->notification->model)
                                        <li style="list-style: none;margin-left: -30px"><span>&nbsp<img src="{{$notification -> users -> profile_pic}}" class="img-circle"  style="width:6%;height: 5%" alt="User Image" />
                                                <a href="{!! route('ticket.thread', $notification->notification->model_id) !!}" id='{{ $notification -> notification_id}}' class='noti_User'>
                                                    {!! $notification->notification->type->message !!} with id "{!!$notification->notification->model->ticket_number!!}"
                                                </a></span>
                                        </li>
                                        @endif
                                        @endif
                                        @endforeach
                                        @endif
                                    </ul>
                            </li>
                            <li class="footer no-border"><div class="col-md-5"></div><div class="col-md-2">
                                    <img src="{{asset("lb-faveo/media/images/gifloader.gif")}}" style="display: none;" id="notification-loader">
                                </div><div class="col-md-5"></div></li>
                            <li class="footer"><a href="{{ url('notifications-list')}}">View all</a>
                            </li>
                        </ul>
                        </li>
                        <!-- User Account: style can be found in dropdown.less -->
                        <li class="dropdown user user-menu">
                            <a href="#" class="dropdown-toggle" data-toggle="dropdown">
                                @if($auth_user_id)
                                <img src="{{$auth_user_profile_pic}}"class="user-image" alt="User Image"/>
                                <span class="hidden-xs">{{$auth_name}}</span>
                                @endif
                            </a>
                            <ul class="dropdown-menu">
                                <!-- User image -->
                                <li class="user-header"  style="background-color:#343F44;">
                                    <img src="{{$auth_user_profile_pic}}" class="img-circle" alt="User Image" />
                                    <p>
                                        {{$auth_name}} - {{$auth_user_role}}
                                        <small></small>
                                    </p>
                                </li>
                                <!-- Menu Footer-->
                                <li class="user-footer" style="background-color:#1a2226;">
                                    <div class="pull-left">
                                        <a href="{{URL::route('client.profile')}}" class="btn btn-info btn-sm"><b>{!! Lang::get('lang.profile') !!}</b></a>
                                    </div>
                                    <div class="pull-right">
                                        <a href="{{url('auth/logout')}}" class="btn btn-danger btn-sm"><b>{!! Lang::get('lang.sign_out') !!}</b></a>
                                    </div>
                                </li>

                            </ul>

                        </li>

                        </ul>

                    </div>

                </nav>
            </header>
            <!-- Left side column. contains the logo and sidebar -->
            <aside class="main-sidebar">
                <!-- sidebar: style can be found in sidebar.less -->
                <section class="sidebar">
                    <div class="user-panel">
                        @if (trim($__env->yieldContent('profileimg')))
                        <h1>@yield('profileimg')</h1>
                        @else
                        <div class = "row">
                            <div class="col-xs-3"></div>
                            <div class="col-xs-2" style="width:50%;">
                                <a href="{!! url('client-profile') !!}">
                                    <img src="{{$auth_user_profile_pic}}" class="img-circle" alt="User Image" />
                                </a>
                            </div>
                        </div>
                        @endif
                        <div class="info" style="text-align:center;">
                            @if($auth_user_id)
                            <p>{{$auth_name}}</p>
                            @endif
                            @if($auth_user_id && $auth_user_active==1)
                            <a href="#"><i class="fa fa-circle text-success"></i> Online</a>
                            @else
                            <a href="#"><i class="fa fa-circle"></i> Offline</a>
                            @endif
                            @if($auth_user_id)
                            <p><a href="{{url('auth/logout')}}" class="btn btn-danger btn-xs"><b>{!! Lang::get('lang.sign_out') !!}</b></a></p>
                            @endif
                        </div>
                    </div>
                    <ul id="side-bar" class="sidebar-menu">
                        @yield('sidebar')
                        <li class="header">{!! Lang::get('lang.Cdn') !!}</li>
                        
                        <li @yield('resources')>
                             <a href="{{ url('/resources')}}" id="load-resources">
                                <i class="fa fa-bolt"></i> <span>{!! Lang::get('lang.resources') !!}</span>
                                <small class="label pull-right bg-green">{{$resources->where('org_id', $user_org->where('user_id', \Auth::user()->id)->first()->org_id)->count()}}</small>
                            </a>
                        </li>

                        <li class="header">{!! Lang::get('lang.Tickets') !!}</li>
                        <li @yield('mytickets')>
                             <a href="{{ url('/mytickets')}}" id="load-myticket">
                                <i class="fa fa-envelope"></i> <span>{!! Lang::get('lang.tickets') !!}</span> <small class="label pull-right bg-green">{{$tickets->where('user_id', '=', Auth::user()->id)->where('status', '=', 1)-> count()}}</small>                                            
                            </a>
                        <li @yield('newticket')>
                             <a href="{{ url('/create-ticket')}}" id="load-createticket">
                                <i class="fa fa-pencil"></i> <span>{!! Lang::get('lang.create_ticket') !!}</span>
                            </a>
                        </li>
                        </li>

                    </ul>
                </section>
                <!-- /.sidebar -->
            </aside>
            <!-- Right side column. Contains the navbar and content of the page -->
            <div class="content-wrapper">
                <!-- Content Header (Page header) -->
                <div class="tab-content" style="background-color: white;padding: 0 0px 0 20px">
                    <div class="collapse navbar-collapse" id="navbar-collapse">
                        <div class="tabs-content">
                            <div class="tabs-pane @yield('dashboard-bar')"  id="tabA">
                                <ul class="nav navbar-nav">
                                </ul>
                            </div>
                            <div class="tabs-pane @yield('cdn-bar')" id="tabB">
                                <ul class="nav navbar-nav">
                                    <li id="bar" @yield('resources')><a href="{{ url('/resources')}}" id="load-open">{!! Lang::get('lang.resources') !!}</a></li>
                                    <li id="bar" @yield('newresource')><a href="{{ route('resource.create')}}" >{!! Lang::get('lang.create_resource') !!}</a></li>
                                </ul>
                            </div>
                            <div class="tabs-pane @yield('ticket-bar')" id="tabC">
                                <ul class="nav navbar-nav">
                                    <li id="bar" @yield('mytickets')><a href="{{ url('/mytickets')}}" id="load-open">{!! Lang::get('lang.tickets') !!}</a></li>
                                    <li id="bar" @yield('newticket')><a href="{{ url('/create-ticket')}}" >{!! Lang::get('lang.create_ticket') !!}</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <section class="content-header">
                    @yield('PageHeader')
                    {!! Breadcrumbs::renderIfExists() !!}
                </section>
                <!-- Main content -->
                <section class="content">
                    @include('themes.default1.layouts.notice')
                    @yield('content')
                </section><!-- /.content -->
            </div>
            <footer class="main-footer">
                <div class="pull-right hidden-xs">
                    <b>Version</b> {!! Config::get('app.version') !!}
                </div>
                <strong>{!! Lang::get('lang.copyright') !!} &copy; {!! date('Y') !!}  <a href="{!! $company->website !!}" target="_blank">{!! $company->company_name !!}</a>.</strong> {!! Lang::get('lang.all_rights_reserved') !!}.
            </footer>
        </div><!-- ./wrapper -->

        <script src="{{asset("lb-faveo/js/ajax-jquery.min.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/js/bootstrap-datetimepicker4.7.14.min.js")}}" type="text/javascript"></script>
        <!-- Bootstrap 3.3.2 JS -->
        <script src="{{asset("lb-faveo/js/bootstrap.min.js")}}" type="text/javascript"></script>
        <!-- Slimscroll -->
        <script src="{{asset("lb-faveo/plugins/slimScroll/jquery.slimscroll.min.js")}}" type="text/javascript"></script>
        <!-- FastClick -->
        <script src="{{asset("lb-faveo/plugins/fastclick/fastclick.min.js")}}"  type="text/javascript"></script>
        <!-- AdminLTE App -->
        <script src="{{asset("lb-faveo/js/app.min.js")}}" type="text/javascript"></script>

        <!-- iCheck -->
        <script src="{{asset("lb-faveo/plugins/iCheck/icheck.min.js")}}" type="text/javascript"></script>
        <!-- jquery ui -->
        <script src="{{asset("lb-faveo/js/jquery.ui.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/plugins/datatables/dataTables.bootstrap.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/plugins/datatables/jquery.dataTables.js")}}" type="text/javascript"></script>
        <!-- Page Script -->
        <script src="{{asset("lb-faveo/js/jquery.dataTables1.10.10.min.js")}}" type="text/javascript" ></script>

        <script type="text/javascript" src="{{asset("lb-faveo/plugins/datatables/dataTables.bootstrap.js")}}"  type="text/javascript"></script>

        <script src="{{asset("lb-faveo/js/jquery.rating.pack.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/plugins/select2/select2.full.min.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/plugins/moment/moment.js")}}" type="text/javascript"></script>

        <!-- full calendar-->
        <script src="{{asset('lb-faveo/plugins/fullcalendar/fullcalendar.min.js')}}" type="text/javascript"></script>
        <script src="{{asset('lb-faveo/plugins/daterangepicker/daterangepicker.js')}}" type="text/javascript"></script>
        <script>
                    $(document).ready(function () {

            $('.noti_User').click(function () {
            var id = this.id;
                    var dataString = 'id=' + id;
                    $.ajax
                    ({
                    type: "POST",
                            url: "{{url('mark-read')}}" + "/" + id,
                            data: dataString,
                            cache: false,
                            success: function (html)
                            {
                            }
                    });
            });
            });
                    $('#read-all').click(function () {

            var id2 = <?php echo $auth_user_id ?>;
                    var dataString = 'id=' + id2;
                    $.ajax
                    ({
                    type: "POST",
                            url: "{{url('mark-all-read')}}" + "/" + id2,
                            data: dataString,
                            cache: false,
                            beforeSend: function () {
                            $('#myDropdown').on('hide.bs.dropdown', function () {
                            return false;
                            });
                                    $("#refreshNote").hide();
                                    $("#notification-loader").show();
                            },
                            success: function (response) {
                            $("#refreshNote").load("<?php echo $_SERVER['REQUEST_URI']; ?>  #refreshNote");
                                    $("#notification-loader").hide();
                                    $('#myDropdown').removeClass('open');
                            }
                    });
            });</script>
        <script>
                    $(function() {
                    // Enable check and uncheck all functionality
                    $(".checkbox-toggle").click(function() {
                    var clicks = $(this).data('clicks');
                            if (clicks) {
                    //Uncheck all checkboxes
                    $("input[type='checkbox']", ".mailbox-messages").iCheck("uncheck");
                    } else {
                    //Check all checkboxes
                    $("input[type='checkbox']", ".mailbox-messages").iCheck("check");
                    }
                    $(this).data("clicks", !clicks);
                    });
                            //Handle starring for glyphicon and font awesome
                            $(".mailbox-star").click(function(e) {
                    e.preventDefault();
                            //detect type
                            var $this = $(this).find("a > i");
                            var glyph = $this.hasClass("glyphicon");
                            var fa = $this.hasClass("fa");
                            //Switch states
                            if (glyph) {
                    $this.toggleClass("glyphicon-star");
                            $this.toggleClass("glyphicon-star-empty");
                    }
                    if (fa) {
                    $this.toggleClass("fa-star");
                            $this.toggleClass("fa-star-o");
                    }
                    });
                    });</script>

        <script src="{{asset("lb-faveo/js/tabby.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/plugins/filebrowser/plugin.js")}}" type="text/javascript"></script>

        <script src="{{asset("lb-faveo/plugins/bootstrap-wysihtml5/bootstrap3-wysihtml5.all.min.js")}}" type="text/javascript"></script>

        <script type="text/javascript">
                    $.ajaxSetup({
                    headers: { 'X-CSRF-Token' : $('meta[name=_token]').attr('content') }
                    });</script>
        <script type="text/javascript">
                    function clickDashboard(e) {
                    if (e.ctrlKey === true) {
                    window.open('{{URL::route("home")}}', '_blank');
                    } else {
                    window.location = "{{URL::route('home')}}";
                    }
                    }

            function clickReport(e) {
            if (e.ctrlKey === true) {
            window.open('{{URL::route("report.index")}}', '_blank');
            } else {
            window.location = "{{URL::route('report.index')}}";
            }
            }
        </script>
        <script>
</script>
<?php Event::fire('show.calendar.script', array()); ?>
<?php Event::fire('load-calendar-scripts', array()); ?>
        @yield('FooterInclude')
    </body>
</html>
