@extends('themes.default1.agent.layout.agent')

@section('Dashboard')
class="active"
@stop

@section('dashboard-bar')
active
@stop

@section('PageHeader')
<h1>{!! Lang::get('lang.dashboard_reports') !!}</h1>
@stop

@section('dashboard')
class="active"
@stop

@section('content')
<!-- check whether success or not -->
{{-- Success message --}}
@if(Session::has('success'))
<div class="alert alert-success alert-dismissable">
    <i class="fa  fa-check-circle"></i>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('success')}}
</div>
@endif
{{-- failure message --}}
@if(Session::has('fails'))
<div class="alert alert-danger alert-dismissable">
    <i class="fa fa-ban"></i>
    <b>{!! Lang::get('lang.alert') !!}!</b>
    <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
    {{Session::get('fails')}}
</div>
@endif
<link type="text/css" href="{{asset("lb-faveo/css/bootstrap-datetimepicker4.7.14.min.css")}}" rel="stylesheet">
{{-- <script src="{{asset("lb-faveo/dist/js/bootstrap-datetimepicker4.7.14.min.js")}}" type="text/javascript"></script> --}}
<div class="row">
    <!-- <div class="col-md-3 col-sm-6 col-xs-12"> -->
    <div class="col-md-2" style="width:20%;">
        <a href="{!! route('inbox.ticket') !!}">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-envelope-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{!! Lang::get('lang.inbox') !!}</span>
                    <span class="info-box-number"><?php echo $tickets->count() ?> <small> {!! Lang::get('lang.tickets') !!}</small></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </a>
    </div><!-- /.col -->
    <!-- <div class="col-md-3 col-sm-6 col-xs-12"> -->
    <div class="col-md-2" style="width:20%;">
        <a href="{!! route('unassigned') !!}">
            <div class="info-box">
                <span class="info-box-icon bg-orange"><i class="fa fa-user-times"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{!! Lang::get('lang.unassigned') !!}</span>
                    <span class="info-box-number">{{$unassigned->count() }} <small> {!! Lang::get('lang.tickets') !!}</small></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </a>
    </div><!-- /.col -->

    <!-- fix for small devices only -->
    <div class="clearfix visible-sm-block"></div>

    <!-- <div class="col-md-3 col-sm-6 col-xs-12"> -->
    <div class="col-md-2" style="width:20%;">
        <a href="{!! route('overdue.ticket') !!}">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="fa fa-calendar-times-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{!! Lang::get('lang.overdue') !!}</span>
                    <span class="info-box-number">{{ $overdues->count() }} <small> Tickets</small></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </a>
    </div><!-- /.col -->
    <!-- <div class="col-md-3 col-sm-6 col-xs-12"> -->
    <div class="col-md-2" style="width:20%;">
        <a href="{!! route('myticket.ticket') !!}">
            <div class="info-box">
                <span class="info-box-icon bg-yellow"><i class="fa fa-user"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{!! Lang::get('lang.my_tickets') !!}</span>
                    <span class="info-box-number">{{$myticket->count() }} <small> Tickets</small></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </a>
    </div><!-- /.col -->


     <div class="col-md-2" style="width:20%;">
     <?php
      if (Auth::user()->role == 'admin') {
            $todaytickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 1)->whereDate('tickets.duedate','=', \Carbon\Carbon::now()->format('Y-m-d'))->count();
        } else {
            $dept =  App\Model\helpdesk\Agent\Department::where('id', '=', Auth::user()->primary_dpt)->first();
     $todaytickets = App\Model\helpdesk\Ticket\Tickets::where('status', '=', 1)->whereDate('tickets.duedate','=', \Carbon\Carbon::now()->format('Y-m-d'))->where('dept_id', '=', $dept->id)->count();
        }
      ?>
                 <a href="{!! route('ticket.duetoday') !!}">
            <div class="info-box">
                <span class="info-box-icon bg-red"><i class="glyphicon glyphicon-eye-open"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{!! Lang::get('lang.duetoday') !!}</span>
                    <span class="info-box-number">{{ $todaytickets }} <small> Tickets</small></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </a>
          <!-- /.info-box -->
        </div>

</div>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">{!! Lang::get('lang.ticket') !!} {!! Lang::get('lang.report') !!}</h3>
    </div>
    <div class="box-body">
        <form id="foo">
            <div  class="form-group">
                <div class="row">
                    <div class='col-sm-2'>
                        {!! Form::label('date', 'Start Date:',['class' => 'lead']) !!}
                        {!! Form::text('start_date',null,['class'=>'form-control','id'=>'datepicker4'])!!}
                    </div>
                    <?php
                    $start_date = App\Model\helpdesk\Ticket\Tickets::where('id', '=', '1')->first();
                    if ($start_date != null) {
                        $created_date = $start_date->created_at;
                        $created_date = explode(' ', $created_date);
                        $created_date = $created_date[0];
                        $start_date = date("m/d/Y", strtotime($created_date . ' -1 months'));
                    } else {
                        $start_date = date("m/d/Y", strtotime(date("m/d/Y") . ' -1 months'));
                    }
                    ?>
                    <script type="text/javascript">
                        $(function () {
                            var timestring1 = "{!! $start_date !!}";
                            var timestring2 = "{!! date('m/d/Y') !!}";
                            $('#datepicker4').datetimepicker({
                                format: 'DD/MM/YYYY',
                                minDate: moment(timestring1).startOf('day'),
                                maxDate: moment(timestring2).startOf('day')
                            });
                            //                $('#datepicker').datepicker()
                        });
                    </script>
                    <div class='col-sm-2'>
                        {!! Form::label('start_time', 'End Date:' ,['class' => 'lead']) !!}
                        {!! Form::text('end_date',null,['class'=>'form-control','id'=>'datetimepicker3'])!!}
                    </div>
                    <script type="text/javascript">
                        $(function () {
                            var timestring1 = "{!! $start_date !!}";
                            var timestring2 = "{!! date('m/d/Y') !!}";
                            $('#datetimepicker3').datetimepicker({
                                format: 'DD/MM/YYYY',
                                minDate: moment(timestring1).startOf('day'),
                                maxDate: moment(timestring2).startOf('day')
                            });
                        });
                    </script>
                    <div class='col-sm-1'>
                        {!! Form::label('filter', 'Filter:',['class' => 'lead']) !!}<br>
                        <input type="submit" class="btn btn-primary">
                    </div>
                </div>
                <div class="row">
                    <div class="col-sm-1"></div>
                    <div class="col-sm-2" style="margin-bottom:-20px;">
                        <label class="lead">{!! Lang::get('lang.Legend') !!}:</label>
                    </div>
                    <style>
                        #legend-holder { border: 1px solid #ccc; float: left; width: 25px; height: 25px; margin: 1px; }
                    </style>
                    <div class="col-md-3"><span id="legend-holder" style="background-color: #6C96DF;"></span>&nbsp; <span class="lead"> <span id="total-created-tickets" ></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.created') !!}</span></div> 
                    <div class="col-md-3"><span id="legend-holder" style="background-color: #6DC5B2;"></span>&nbsp; <span class="lead"> <span id="total-reopen-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.reopen') !!}</span></div> 
                    <div class="col-md-3"><span id="legend-holder" style="background-color: #E3B870;"></span>&nbsp; <span class="lead"> <span id="total-closed-tickets" class="lead"></span> {!! Lang::get('lang.tickets') !!} {!! Lang::get('lang.closed') !!}</span></div> 
                </div>
            </div>
        </form>
        <!--<div id="legendDiv"></div>-->
        <div id="chart-tickets" class="chart">
            <canvas class="chart-data" id="tickets-graph" width="1000" height="250"></canvas>   
        </div>
    </div><!-- /.box-body -->
</div><!-- /.box -->
<div class="box">
    <div class="box-header with-border  ">
        <h1 class="box-title">{!! Lang::get('lang.ticket') !!}  {!! Lang::get('lang.statistics') !!}</h1>
    </div>
    <div class="box-body">
        <table class="table table-hover table-bordered">
            <?php
//            dd($department);
            $flattened = $department->flatMap(function ($values) {
                return $values->keyBy('status');
            });
            $statuses = $flattened->keys();
            ?>
            <tr>
                <th>Department</th>
                @forelse($statuses as $status)
                 <th>{!! $status !!}</th>
                @empty 
                
                @endforelse

            </tr>
            @foreach($department as $name=>$dept)
            <tr>
                <td>{!! $name !!}</td>
                @forelse($statuses as $status)
                @if($dept->get($status))
                 <th>{!! $dept->get($status)->count !!}</th>
                @else 
                    <th></th>
                 @endif
                @empty 
                
                @endforelse
            </tr>
            @endforeach 
        </table>
    </div>
</div>
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">{!! Lang::get('lang.cdn_traffic_report') !!}</h3>
    </div>
    <div class="box-body">
        <form id="f_traffic">
            <div  class="form-group">
                <div class="row">
                    <div class='col-sm-2'>
                        {!! Form::label('date', 'Start Date:',['class' => 'lead']) !!}
                        {!! Form::text('sdate',null,['class'=>'form-control','id'=>'cdn_traffic_sdate'])!!}
                    </div>
                    <?php
                    $start_date = App\Model\Cdn\CdnDailyReport::min('report_date');
                    $end_date = App\Model\Cdn\CdnDailyReport::max('report_date');
                    ?>
                    <script type="text/javascript">
                        $(function () {
                            var timestring1 = "{!! $start_date !!}";
                            var timestring2 = "{!! $end_date !!}";
                            $('#cdn_traffic_sdate').datetimepicker({
                                format: 'YYYY-MM-DD',
                                minDate: moment(timestring1).startOf('day'),
                                maxDate: moment(timestring2).startOf('day')
                            });
                            //                $('#datepicker').datepicker()
                        });
                    </script>
                    <div class='col-sm-2'>
                        {!! Form::label('start_time', 'End Date:' ,['class' => 'lead']) !!}
                        {!! Form::text('edate',null,['class'=>'form-control','id'=>'cdn_traffic_edate'])!!}
                    </div>
                    <script type="text/javascript">
                        $(function () {
                            var timestring1 = "{!! $start_date !!}";
                            var timestring2 = "{!! $end_date !!}";
                            $('#cdn_traffic_edate').datetimepicker({
                                format: 'YYYY-MM-DD',
                                minDate: moment(timestring1).startOf('day'),
                                maxDate: moment(timestring2).startOf('day')
                            });
                        });
                    </script>
                    <div class='col-sm-2'>
                    <?php
                        $resource_list = $resources->selectRaw('CONCAT(id, " - ", cdn_hostname) as full_resource, id')->pluck('full_resource', 'id')->toArray();
                    ?>
                        {!! Form::label('resource', Lang::get('lang.resource').':',['class' => 'lead']) !!}
                        {!! Form::select('resource_id',[''=>Lang::get('lang.all'),Lang::get('lang.resource')=>$resource_list],null,['class' => 'form-control','id'=>'cdn_traffic_resource_id']) !!}
                    </div>
                    <div class='col-sm-1'>
                        {!! Form::label('filter', 'Filter:',['class' => 'lead']) !!}<br>
                        <input type="submit" class="btn btn-primary">
                    </div>
                </div>
            </div>
        </form>
        <div id="legend-traffic">
            <div class="row">
                <div class="col-md-3"></div> 
                <div class="col-md-6 col-md-offset-5"><span class="lead">{!! Lang::get('lang.total') !!}: <span id="total-kb" class="lead"></span></span></div> 
                <div class="col-md-3"></div> 
            </div>            
        </div>
        <div id="chart-traffic" class="chart">
            <canvas class="chart-data" id="traffic-graph" width="1000" height="250"></canvas>   
        </div>
    </div><!-- /.box-body -->
</div><!-- /.box -->
<div class="box box-info">
    <div class="box-header with-border">
        <h3 class="box-title">{!! Lang::get('lang.cdn_summary_report') !!}</h3>
    </div>
    <div class="box-body">
        <form id="summary_report">
            <div  class="form-group">
                <div class="row">
                    <div class='col-sm-2'>
                        {!! Form::label('date', Lang::get('lang.date').':',['class' => 'lead']) !!}
                        {!! Form::text('report_date',null,['class'=>'form-control','id'=>'report_date'])!!}
                    </div>
                    <script type="text/javascript">
                        $(function () {
                            var timestring1 = "02/01/2017";
                            var timestring2 = "{!! date('m/d/Y', strtotime('-1 day')) !!}";
                            $('#report_date').datetimepicker({
                                format: 'DD-MM-YYYY',
                                minDate: moment(timestring1).startOf('day'),
                                maxDate: moment(timestring2).startOf('day')
                            });
                        });
                    </script>
                    <div class='col-sm-2'>
                    <?php
                        $resource_list = $resources->selectRaw('CONCAT(id, " - ", cdn_hostname) as full_resource, id')->pluck('full_resource', 'id')->toArray();
                    ?>
                        {!! Form::label('resource', Lang::get('lang.resource').':',['class' => 'lead']) !!}
                        {!! Form::select('resource_id',[''=>Lang::get('lang.select'),Lang::get('lang.resource')=>$resource_list],null,['class' => 'form-control','id'=>'resource_id']) !!}
                    </div>
                    <div class='col-sm-1'>
                        {!! Form::label('filter', '&nbsp;', ['class' => 'lead']) !!}<br>
                        <input type="submit" class="btn btn-primary">
                    </div>
                </div>
            </div>
        </form>
        <!--<div id="legendDiv"></div>-->
        <div id="chart-report" class="chart">
            <!--canvas class="chart-data" id="report-graph" width="1000" height="500"></canvas-->
            <img id="report-graph" src='/blank.png' onerror="this.src='/404.png'">
        </div>
    </div><!-- /.box-body -->
</div><!-- /.box -->
<div id="refresh"> 
    <script src="{{asset("lb-faveo/plugins/chartjs/Chart.min.js")}}" type="text/javascript"></script>
</div>
<script src="{{asset("lb-faveo/plugins/chartjs/Chart.min.js")}}" type="text/javascript"></script>
<script type="text/javascript">
                        $(document).ready(function () {
                            $.getJSON("agen", function (result) {
                                var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;
                                //,data2=[],data3=[],data4=[];
                                for (var i = 0; i < result.length; i++) {
                                    // $var12 = result[i].day;
                                    // labels.push($var12);
                                    labels.push(result[i].date);
                                    open.push(result[i].open);
                                    closed.push(result[i].closed);
                                    reopened.push(result[i].reopened);
                                    // data4.push(result[i].open);
                                    open_total += parseInt(result[i].open);
                                    closed_total += parseInt(result[i].closed);
                                    reopened_total += parseInt(result[i].reopened);
                                }
                                var buyerData = {
                                    labels: labels,
                                    datasets: [
                                        {
                                            label: "Open Tickets",
                                            fillColor: "rgba(93, 189, 255, 0.05)",
                                            strokeColor: "rgba(2, 69, 195, 0.9)",
                                            pointColor: "rgba(2, 69, 195, 0.9)",
                                            pointStrokeColor: "#c1c7d1",
                                            pointHighlightFill: "#fff",
                                            pointHighlightStroke: "rgba(220,220,220,1)",
                                            data: open
                                        }
                                        , {
                                            label: "Closed Tickets",
                                            fillColor: "rgba(255, 206, 96, 0.08)",
                                            strokeColor: "rgba(221, 129, 0, 0.94)",
                                            pointColor: "rgba(221, 129, 0, 0.94)",
                                            pointStrokeColor: "rgba(60,141,188,1)",
                                            pointHighlightFill: "#fff",
                                            pointHighlightStroke: "rgba(60,141,188,1)",
                                            data: closed

                                        }
                                        , {
                                            label: "Reopened Tickets",
                                            fillColor: "rgba(104, 255, 220, 0.06)",
                                            strokeColor: "rgba(0, 149, 115, 0.94)",
                                            pointColor: "rgba(0, 149, 115, 0.94)",
                                            pointStrokeColor: "rgba(60,141,188,1)",
                                            pointHighlightFill: "#fff",
                                            pointHighlightStroke: "rgba(60,141,188,1)",
                                            data: reopened
                                        }
                                    ]
                                };
                                $("#total-created-tickets").html(open_total);
                                $("#total-reopen-tickets").html(reopened_total);
                                $("#total-closed-tickets").html(closed_total);
                                var myLineChart = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                                    showScale: true,
                                    //Boolean - Whether grid lines are shown across the chart
                                    scaleShowGridLines: true,
                                    //String - Colour of the grid lines
                                    scaleGridLineColor: "rgba(0,0,0,.05)",
                                    //Number - Width of the grid lines
                                    scaleGridLineWidth: 1,
                                    //Boolean - Whether to show horizontal lines (except X axis)
                                    scaleShowHorizontalLines: true,
                                    //Boolean - Whether to show vertical lines (except Y axis)
                                    scaleShowVerticalLines: true,
                                    //Boolean - Whether the line is curved between points
                                    bezierCurve: true,
                                    //Number - Tension of the bezier curve between points
                                    bezierCurveTension: 0.3,
                                    //Boolean - Whether to show a dot for each point
                                    pointDot: true,
                                    //Number - Radius of each point dot in pixels
                                    pointDotRadius: 1,
                                    //Number - Pixel width of point dot stroke
                                    pointDotStrokeWidth: 1,
                                    //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                                    pointHitDetectionRadius: 10,
                                    //Boolean - Whether to show a stroke for datasets
                                    datasetStroke: true,
                                    //Number - Pixel width of dataset stroke
                                    datasetStrokeWidth: 1,
                                    //Boolean - Whether to fill the dataset with a color
                                    datasetFill: true,
                                    //String - A legend template
                                    //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                    maintainAspectRatio: true,
                                    //Boolean - whether to make the chart responsive to window resizing
                                    responsive: true

                                });
                                //document.getElementById("legendDiv").innerHTML = myLineChart.generateLegend();
                            });
                            $('#click me').click(function () {
                                $('#foo').submit();
                            });
                            $('#foo').submit(function (event) {
                                // get the form data
                                // there are many ways to get this data using jQuery (you can use the class or id also)
                                var date1 = $('#datepicker4').val();
                                var date2 = $('#datetimepicker3').val();
                                var formData = date1.split("/").join('-');
                                var dateData = date2.split("/").join('-');
                                //$('#foo').serialize();
                                // process the form
                                $.ajax({
                                    type: 'POST', // define the type of HTTP verb we want to use (POST for our form)
                                    url: 'chart-range/' + dateData + '/' + formData, // the url where we want to POST
                                    data: formData, // our data object
                                    dataType: 'json', // what type of data do we expect back from the server

                                    success: function (result2) {
                                        //  $.getJSON("agen", function (result) {
                                        var labels = [], open = [], closed = [], reopened = [], open_total = 0, closed_total = 0, reopened_total = 0;
                                        //,data2=[],data3=[],data4=[];
                                        for (var i = 0; i < result2.length; i++) {
                                            // $var12 = result[i].day;
                                            // labels.push($var12);
                                            labels.push(result2[i].date);
                                            open.push(result2[i].open);
                                            closed.push(result2[i].closed);
                                            reopened.push(result2[i].reopened);
                                            // data4.push(result[i].open);
                                            open_total += parseInt(result2[i].open);
                                            closed_total += parseInt(result2[i].closed);
                                            reopened_total += parseInt(result2[i].reopened);
                                        }

                                        var buyerData = {
                                            labels: labels,
                                            datasets: [
                                                {
                                                    label: "Open Tickets",
                                                    fillColor: "rgba(93, 189, 255, 0.05)",
                                                    strokeColor: "rgba(2, 69, 195, 0.9)",
                                                    pointColor: "rgba(2, 69, 195, 0.9)",
                                                    pointStrokeColor: "#c1c7d1",
                                                    pointHighlightFill: "#fff",
                                                    pointHighlightStroke: "rgba(220,220,220,1)",
                                                    data: open
                                                }
                                                , {
                                                    label: "Closed Tickets",
                                                    fillColor: "rgba(255, 206, 96, 0.08)",
                                                    strokeColor: "rgba(221, 129, 0, 0.94)",
                                                    pointColor: "rgba(221, 129, 0, 0.94)",
                                                    pointStrokeColor: "rgba(60,141,188,1)",
                                                    pointHighlightFill: "#fff",
                                                    pointHighlightStroke: "rgba(60,141,188,1)",
                                                    data: closed

                                                }
                                                , {
                                                    label: "Reopened Tickets",
                                                    fillColor: "rgba(104, 255, 220, 0.06)",
                                                    strokeColor: "rgba(0, 149, 115, 0.94)",
                                                    pointColor: "rgba(0, 149, 115, 0.94)",
                                                    pointStrokeColor: "rgba(60,141,188,1)",
                                                    pointHighlightFill: "#fff",
                                                    pointHighlightStroke: "rgba(60,141,188,1)",
                                                    data: reopened
                                                }
                                                // ,{
                                                //       label : "Reopened Tickets",
                                                //         fillColor : "rgba(102,255,51,0.2)",
                                                //       strokeColor : "rgba(151,187,205,1)",
                                                //        pointColor : "rgba(46,184,0,1)",
                                                //         pointStrokeColor : "#fff",
                                                //         pointHighlightFill : "#fff",
                                                //         pointHighlightStroke : "rgba(151,187,205,1)",
                                                //        data : data3
                                                //     }
                                            ]
                                        };
                                        $("#total-created-tickets").html(open_total);
                                        $("#total-reopen-tickets").html(reopened_total);
                                        $("#total-closed-tickets").html(closed_total);
                                        var myLineChart = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                                            showScale: true,
                                            //Boolean - Whether grid lines are shown across the chart
                                            scaleShowGridLines: true,
                                            //String - Colour of the grid lines
                                            scaleGridLineColor: "rgba(0,0,0,.05)",
                                            //Number - Width of the grid lines
                                            scaleGridLineWidth: 1,
                                            //Boolean - Whether to show horizontal lines (except X axis)
                                            scaleShowHorizontalLines: true,
                                            //Boolean - Whether to show vertical lines (except Y axis)
                                            scaleShowVerticalLines: true,
                                            //Boolean - Whether the line is curved between points
                                            bezierCurve: true,
                                            //Number - Tension of the bezier curve between points
                                            bezierCurveTension: 0.3,
                                            //Boolean - Whether to show a dot for each point
                                            pointDot: true,
                                            //Number - Radius of each point dot in pixels
                                            pointDotRadius: 1,
                                            //Number - Pixel width of point dot stroke
                                            pointDotStrokeWidth: 1,
                                            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                                            pointHitDetectionRadius: 10,
                                            //Boolean - Whether to show a stroke for datasets
                                            datasetStroke: true,
                                            //Number - Pixel width of dataset stroke
                                            datasetStrokeWidth: 1,
                                            //Boolean - Whether to fill the dataset with a color
                                            datasetFill: true,
                                            //String - A legend template
                                            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                            maintainAspectRatio: true,
                                            //Boolean - whether to make the chart responsive to window resizing
                                            responsive: true

                                        });
                                        myLineChart.options.responsive = false;
                                        $("#tickets-graph").remove();
                                        $("#chart-tickets").html("<canvas id='tickets-graph' width='1000' height='250'></canvas>");
                                        var myLineChart1 = new Chart(document.getElementById("tickets-graph").getContext("2d")).Line(buyerData, {
                                            showScale: true,
                                            //Boolean - Whether grid lines are shown across the chart
                                            scaleShowGridLines: true,
                                            //String - Colour of the grid lines
                                            scaleGridLineColor: "rgba(0,0,0,.05)",
                                            //Number - Width of the grid lines
                                            scaleGridLineWidth: 1,
                                            //Boolean - Whether to show horizontal lines (except X axis)
                                            scaleShowHorizontalLines: true,
                                            //Boolean - Whether to show vertical lines (except Y axis)
                                            scaleShowVerticalLines: true,
                                            //Boolean - Whether the line is curved between points
                                            bezierCurve: true,
                                            //Number - Tension of the bezier curve between points
                                            bezierCurveTension: 0.3,
                                            //Boolean - Whether to show a dot for each point
                                            pointDot: true,
                                            //Number - Radius of each point dot in pixels
                                            pointDotRadius: 1,
                                            //Number - Pixel width of point dot stroke
                                            pointDotStrokeWidth: 1,
                                            //Number - amount extra to add to the radius to cater for hit detection outside the drawn point
                                            pointHitDetectionRadius: 10,
                                            //Boolean - Whether to show a stroke for datasets
                                            datasetStroke: true,
                                            //Number - Pixel width of dataset stroke
                                            datasetStrokeWidth: 1,
                                            //Boolean - Whether to fill the dataset with a color
                                            datasetFill: true,
                                            //String - A legend template
                                            //Boolean - whether to maintain the starting aspect ratio or not when responsive, if set to false, will take up entire container
                                            maintainAspectRatio: true,
                                            //Boolean - whether to make the chart responsive to window resizing
                                            responsive: true
                                        });
                                        document.getElementById("legendDiv").innerHTML = myLineChart1.generateLegend();
                                    }
                                });
                                // using the done promise callback
                                // stop the form from submitting the normal way and refreshing the page
                                event.preventDefault();
                            });

                            $('#f_traffic').submit(function (event) {
                                var sdate = $('#cdn_traffic_sdate').val();
                                var edate = $('#cdn_traffic_edate').val();
                                var resource_id = $('#cdn_traffic_resource_id').val();
                                if (sdate == '' || edate == '') {
                                    alert('{{ Lang::get('lang.date_resource_empty') }}');
                                } else {
                                    $.ajax({
                                        type: 'POST',
                                        url: 'chart-cdn-traffic/' + sdate + '/' + edate + '/' + resource_id,
                                        dataType: 'json',
    
                                        success: function (t_result) {
                                            var report_date = [], total_byte = [], total = 0;
                                            for (var i = 0; i < t_result.length; i++) {
                                                report_date.push(t_result[i].report_date);
                                                total_byte.push(t_result[i].total_byte);
                                                total += t_result[i].total_byte * 1;
                                            }
    
                                            var reportData = {
                                                labels: report_date,
                                                datasets: [
                                                    {
                                                        label: "CDN Traffic (KByte)",
                                                        fillColor: "rgba(60,141,188,0.9)",
                                                        strokeColor: "rgba(60,141,188,0.8)",
                                                        pointColor: "#3b8bba",
                                                        data: total_byte
                                                    }
                                                ]
                                            };
                                            $("#traffic-graph").remove();
                                            $("#total-kb").html(total.toLocaleString() + ' KB');
                                            $("#chart-traffic").html("<canvas id='traffic-graph' width='1000' height='250'></canvas>");
                                            Chart.types.Bar.extend({
                                                name: "BarAlt",
                                                draw: function () {
                                                    Chart.types.Bar.prototype.draw.apply(this, arguments);
                                                    var ctx = this.chart.ctx;
                                                    ctx.save();
                                                    // text alignment and color
                                                    ctx.textAlign = "center";
                                                    ctx.textBaseline = "bottom";
                                                    ctx.fillStyle = this.options.scaleFontColor;
                                                    // position
                                                    var x = this.scale.xScalePaddingLeft * 0.3;
                                                    var y = this.chart.height / 2;
                                                    // change origin
                                                    ctx.translate(x, y)
                                                    // rotate text
                                                    ctx.rotate(-90 * Math.PI / 180);
                                                    ctx.fillText(this.datasets[0].label, 0, 0);
                                                    ctx.restore();
                                                }
                                            });
                                            var barChartOptions = {
                                                scaleBeginAtZero: true,
                                                scaleShowGridLines: true,
                                                scaleGridLineColor: "rgba(0,0,0,.05)",
                                                scaleGridLineWidth: 1,
                                                scaleShowHorizontalLines: true,
                                                scaleShowVerticalLines: true,
                                                barShowStroke: true,
                                                barStrokeWidth: 2,
                                                barValueSpacing: 5,
                                                barDatasetSpacing: 1,
                                                responsive: true,
                                                maintainAspectRatio: true,
                                                tooltipTemplate: "<%= label %>: <%= value %>KB",
                                                datasetFill: false,
                                                scaleLabel: "          <%=parseInt(value).toLocaleString()%>"
                                            };
                                            var ctx = document.getElementById("traffic-graph").getContext("2d");
                                            var barChart = new Chart(ctx).BarAlt(reportData, barChartOptions);
                                            //document.getElementById("legend-traffic").innerHTML = barChart.generateLegend();
                                        }
                                    });
                                }
                                return false;
                                event.preventDefault();
                            });


                            $('#summary_report').submit(function (event) {
                                var report_date = $('#report_date').val();
                                var resource_id = $('#resource_id').val();
                                if (report_date == '' || resource_id == '') {
                                    alert('{{ Lang::get('lang.date_resource_empty') }}');
                                } else {
                                    $('#report-graph').attr("src", 'summary-report/' + report_date + '/' + resource_id);
                   
                                }
                                return false;
                                event.preventDefault();
                            });

                        });
</script>
<script type="text/javascript">
    jQuery(document).ready(function () {
        // Close a ticket
        $('#close').on('click', function (e) {
            $.ajax({
                type: "GET",
                url: "agen",
                beforeSend: function () {

                },
                success: function (response) {

                }
            })
            return false;
        });
<?php
$edate = date('Y-m-d', strtotime('-1 day'.date('Y-m-d')));
$sdate = date('Y-m-d', strtotime('-1 month'.$edate));
?>
        $('#cdn_traffic_sdate').val('{{ $sdate }}');
        $('#cdn_traffic_edate').val('{{ $edate }}');
        $('#f_traffic').submit();
    });
</script>
<script src="{{asset("lb-faveo/plugins/moment-develop/moment.js")}}" type="text/javascript"></script>
<script src="{{asset("lb-faveo/js/bootstrap-datetimepicker4.7.14.min.js")}}" type="text/javascript"></script>
@stop