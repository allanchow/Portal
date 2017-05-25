@extends('themes.default1.client.layout.dashboard')

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
        <a href="{!! route('ticket2') !!}">
            <div class="info-box">
                <span class="info-box-icon bg-aqua"><i class="fa fa-envelope-o"></i></span>
                <div class="info-box-content">
                    <span class="info-box-text">{!! Lang::get('lang.tickets') !!}</span>
                    <span class="info-box-number"><?php echo $tickets->where('user_id', '=', Auth::user()->id)->where('status', '=', 1)->count() ?> <small> {!! Lang::get('lang.tickets') !!}</small></span>
                </div><!-- /.info-box-content -->
            </div><!-- /.info-box -->
        </a>
    </div><!-- /.col -->


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
        <div class="chart">
            <!--canvas class="chart-data" id="report-graph" width="1000" height="500"></canvas-->
            <img id="report-graph" src='/blank.png' onerror="this.src='/404.png'">
        </div>
    </div><!-- /.box-body -->
</div><!-- /.box -->
<div id="refresh"> 
    <script src="https://portal-dev.allbrightnet.com/lb-faveo/plugins/chartjs/Chart.min.js" type="text/javascript"></script>
</div>
<script src="https://portal-dev.allbrightnet.com/lb-faveo/plugins/chartjs/Chart.min.js" type="text/javascript"></script>
<script type="text/javascript">
    $(document).ready(function () {

        $('#f_traffic').submit(function (event) {
            var sdate = $('#cdn_traffic_sdate').val();
            var edate = $('#cdn_traffic_edate').val();
            var resource_id = $('#cdn_traffic_resource_id').val();
            if (sdate == '' || edate == '') {
                alert('{{ Lang::get('lang.date_resource_empty') }}');
            } else {
                $.ajax({
                    type: 'POST',
                    url: '/chart-cdn-traffic/' + sdate + '/' + edate + '/' + resource_id,
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
