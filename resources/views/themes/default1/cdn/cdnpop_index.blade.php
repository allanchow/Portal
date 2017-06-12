@extends('themes.default1.agent.layout.agent')

@section('title')
CDN Resources
@stop

@section('Cdn')
class="active"
@stop

@section('cdn-bar')
active
@stop

@section('cdnpop')
class="active"
@stop

@section('HeadInclude')
    <link href="{{asset('lb-faveo/plugins/pace/pace.css')}}" rel="stylesheet" type="text/css" />
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.Pop')}}</h1>
@stop
@section('content')
<!-- Main content -->
<div class="box box-primary">
    <div class="box-header with-border">
        <div class="row">
        <div>
            <div class="col-md-6">
                <h3 class="box-title ">{{Lang::get('lang.status')}} : <span id='pop_status'></span></h3>
            </div>
            <div class="col-md-6">
                <div class="col-md-5">
                    <div class="box-tools" style="width: 235px">
                        <div class="has-feedback">
                            <input type="text" class="form-control input-sm" id="search-text" name="search" placeholder="{{Lang::get('lang.search')}}" style="height:30px">
                            <span class="fa fa-search form-control-feedback"></span>
                        </div>
                    </div><!-- /.box-tools -->
                </div>
                <div class="col-md-7">
                    <div class="pull-right">
                    <div id="labels-div" class="btn-group">
                        <button type="button" class="btn btn-sm btn-default dropdown-toggle" data-toggle="dropdown" id="labels-button"><i class="fa fa-eye" style="color:teal;">&nbsp;</i>{{Lang::get('lang.view-option')}}<span class="caret"></span>
                        </button>
                        <ul  class="dropdown-menu role="menu">
                            <li class="all"><a href="#" class="all">{{Lang::get('lang.all_status')}}</a></li>
                            <li><a href="#" class="active">{{Lang::get('lang.only_active')}}</a></li>
                            <li><a href="#" class="inactive">{{Lang::get('lang.only_inactive')}}</a></li>
                        </ul>
                    </div>
                    <button type="button" class="btn btn-sm btn-danger" id="attacking">{{Lang::get('lang.ddos_attacking')}}</button>
                    <button type="button" class="btn btn-sm btn-success" id="resume">{{Lang::get('lang.resume')}}</button>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-body">
        {!!$table->render('vendor.Chumper.template')!!}
    </div>
</div>





{!! $table->script('vendor.Chumper.cdnpop-javascript') !!}
@stop

@section('FooterInclude')
    <script src="{{asset("lb-faveo/plugins/pace/pace.js")}}" type="text/javascript"></script>
@stop