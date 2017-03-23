@extends($ext_view)

@section('title')
CDN Resources
@stop

@section('Cdn')
class="active"
@stop

@section('cdn-bar')
active
@stop

@section('resources')
class="active"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.resources')}}</h1>
@stop
@section('content')
@if ($ext_view == 'themes.default1.agent.layout.agent')
    @include('themes.default1.layouts.notice')
@endif
<!-- Main content -->
<div class="box box-primary">
    <div class="box-header with-border">
        <div class="row">
        <div>
            <div class="col-md-6">
                <h3 class="box-title ">{{Lang::get('lang.resources')}}</h3>                
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
                            <li><a href="#" class="pending">{{Lang::get('lang.only_pending')}}</a></li>
                            <li><a href="#" class="revert-dns">{{Lang::get('lang.only_revert-dns')}}</a></li>
                            @if (Auth::user()->role == "admin" or Auth::user()->role == "agent")
                                <li><a href="#" class="suspended">{{Lang::get('lang.only_suspended')}}</a></li>
                                <li><a href="#" class="deleting">{{Lang::get('lang.only_deleting')}}</a></li>
                            @endif
                        </ul>
                    </div>
                    @if ($ext_view == 'themes.default1.agent.layout.agent')
                        <button type="button" class="btn btn-sm btn-warning" id="force_update_button">{{Lang::get('lang.force_update')}}</button>
                    @endif
                    <a href="{{route('resource.create')}}" class="btn btn-primary btn-sm">{{Lang::get('lang.create_resource')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <div class="box-body">
        {!!$table->render('vendor.Chumper.template')!!}
    </div>
</div>





{!! $table->script('vendor.Chumper.resource-javascript') !!}
@stop
