
@extends($ext_view)

<?php
if ($mode == 'create') {
    $tab = 'newresource';
} else {
    $tab = 'resources';
}
?>

@section('Cdn')
class="active"
@stop

@section('cdn-bar')
active
@stop

@section($tab)
class="active"
@stop

@section('PageHeader')
<h1>
@if ($mode == 'create')
    {{ Lang::get('lang.create_resource') }}
@else
    {{ Lang::get('lang.edit_resource') }}
@endif
</h1>
@stop
@section('content')
@if ($ext_view == 'themes.default1.agent.layout.agent')
    @include('themes.default1.layouts.notice')
@endif
<!-- content -->
<!-- open a form -->
@if ($mode == 'create')
{!! Form::open(['route' => 'resource.store']) !!}
@else
{!! Form::model($resource, ['url' => 'resource/'.$resource->id,'method' => 'PATCH'] )!!}
@endif
<!-- <section class="content"> -->
<div class="box box-primary">
    <div class="box-body">
        <div class="row">
            <div class="col-xs-6 form-group {{ $errors->has('cdn_hostname') ? 'has-error' : '' }}">
                {!! Form::label('cdn_hostname',Lang::get('lang.cdn_hostname')) !!}         <span class="text-red"> *</span>       
                {!! Form::text('cdn_hostname',null,['class' => 'form-control']) !!}
            </div>
            @if ($mode == 'edit')
                <div class="col-xs-6 form-group">
                    {!! Form::label('status',Lang::get('lang.status')) !!}       
                    <p>
                    @if ($resource->status == 0)
                        <span class="label label-danger">{!! Lang::get('lang.suspended') !!}</span>
                    @elseif ($resource->status == 1)
                        <span class="label label-warning">{!! Lang::get('lang.pending') !!}</span>
                    @else
                        <span class="label label-success">{!! Lang::get('lang.active') !!}</span>
                    @endif
                    @if ($resource->update_status == 1)
                        <span class="label label-warning">{!! Lang::get('lang.updating') !!}</span>
                    @elseif ($resource->update_status == 2)
                        <span class="label label-warning">{!! Lang::get('lang.deleting') !!}</span>
                    @elseif ($resource->update_status == 3)
                        <span class="label label-warning">{!! Lang::get('lang.pending') !!}</span>
                    @endif
                    @if ($resource->error_msg != '')
                        <span class="label label-danger">{!! Lang::get('lang.error') !!}</span>
                    @endif
                    </p>
                </div>
            @endif
        </div>
        @if (Auth::user()->role == "admin" or Auth::user()->role == "agent")
        <div class="row">
            <div class="col-xs-6 form-group {{ $errors->has('organization') ? 'has-error' : '' }}">
                {!! Form::label('organization',Lang::get('lang.organization')) !!}
                {!! Form::select('org_id',[''=>'Select','Organization'=>$org],null,['class' => 'form-control','id'=>'org']) !!}
                
            </div>
        </div>
        @else
            @if ($mode == 'create')
                 {!! Form::hidden('org_id', $resource->org_id) !!}
            @else
                 {!! Form::hidden('org_id') !!}
            @endif
        @endif
        @if ($mode == 'edit')
        <div class="row">
            <div class="col-xs-6 form-group">
                {!! Form::label('cname',Lang::get('lang.cname')) !!}
                <p>{{ $resource->cname }}</p>
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-xs-4 form-group {{ $errors->has('origin') ? 'has-error' : '' }}">
                {!! Form::label('origin',Lang::get('lang.origin')) !!}         <span class="text-red"> * ({!! Lang::get('lang.one_ip_per_line') !!})</span>
                {!! Form::textarea('origin',null,['class' => 'form-control']) !!}
            </div>
        </div>
        @if ($mode == 'edit')
            <div class="row">
                <div class="col-xs-4 form-group">
                    {!! Form::label('created_at',Lang::get('lang.created')) !!}
                    <p>{{ $resource->created_at }}</p>
                </div>
            </div>
        @endif
    </div>
    <div class="box-footer">
        @if ($mode == 'edit' and ($resource->status < 2 or $resource->update_status > 0) and $resource->error_msg == '')
            {!! Form::submit(Lang::get('lang.submit'),['class'=>'form-group btn btn-primary disabled'])!!}
        @else
            {!! Form::submit(Lang::get('lang.submit'),['class'=>'form-group btn btn-primary'])!!}
        @endif
    </div>
</div>
@stop