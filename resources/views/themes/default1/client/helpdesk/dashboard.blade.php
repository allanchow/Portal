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
        <h3 class="box-title">{!! Lang::get('lang.report') !!}</h3>
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
<script type="text/javascript">
    $(document).ready(function () {
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
<script src="{{asset("lb-faveo/plugins/moment-develop/moment.js")}}" type="text/javascript"></script>
<script src="{{asset("lb-faveo/js/bootstrap-datetimepicker4.7.14.min.js")}}" type="text/javascript"></script>
@stop
