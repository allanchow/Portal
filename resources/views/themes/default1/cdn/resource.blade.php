
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

@section('HeadInclude')
        <link href="{{asset('lb-faveo/plugins/pace/pace.css')}}" rel="stylesheet" type="text/css" />
        <link href="{{asset("lb-faveo/plugins/bootstrap-tagsinput/bootstrap-tagsinput.css")}}" rel="stylesheet" rel="stylesheet"/>    
        <style>
        .modal {
          text-align: center;
        }
        
        @media screen and (min-width: 768px) { 
          .modal:before {
            display: inline-block;
            vertical-align: middle;
            content: " ";
            height: 100%;
          }
        }
        
        .modal-dialog {
          display: inline-block;
          text-align: left;
          vertical-align: middle;
        }
        </style>
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
            <div class="col-md-4 form-group {{ $errors->has('cdn_hostname') ? 'has-error' : '' }}">
                {!! Form::label('cdn_hostname',Lang::get('lang.cdn_hostname')) !!}         <span class="text-red"> *</span>       
                {!! Form::text('cdn_hostname',null,['class' => 'form-control']) !!}
            </div>
            @if ($mode == 'edit')
                <div class="col-md-4 form-group">
                    {!! Form::label('status',Lang::get('lang.status')) !!}       
                    <p>
                    @if ($resource->status == -1)
                        <span class="label label-default">{!! Lang::get('lang.dns_to_origin') !!}</span>
                    @elseif ($resource->status == 0)
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
                    @elseif ($resource->update_status == 3 and (Auth::user()->role == "admin" or Auth::user()->role == "agent"))
                        <span class="label label-warning">{!! Lang::get('lang.pending') !!}</span>
                    @endif
                    @if ($resource->force_update == 1 and (Auth::user()->role == "admin" or Auth::user()->role == "agent"))
                        <span class="label label-warning">{!! Lang::get('lang.force_update') !!}</span>
                    @endif
                    @if ($resource->error_msg != '')
                        <span class="label label-danger">{!! Lang::get('lang.error') !!}</span>
                    @endif
                    </p>
                </div>
            @endif
        </div>
        <div class="row">
        @if ($mode == 'edit')
            <div class="col-md-4 form-group">
                {!! Form::label('cname',Lang::get('lang.cname')) !!}
                <p>{{ $resource->cname }}</p>
            </div>
        @endif
        @if (Auth::user()->role == "admin" or Auth::user()->role == "agent")
            <div class="col-md-4 form-group {{ $errors->has('organization') ? 'has-error' : '' }}">
                {!! Form::label('organization',Lang::get('lang.organization')) !!}
                {!! Form::select('org_id',[''=>'Select','Organization'=>$org],null,['class' => 'form-control','id'=>'org']) !!}
                
            </div>
        @else
            @if ($mode == 'create')
                 {!! Form::hidden('org_id', $resource->org_id) !!}
            @else
                 {!! Form::hidden('org_id') !!}
            @endif
        @endif
        </div>
        @if (Auth::user()->role == "admin" or Auth::user()->role == "agent")
        <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('host_header') ? 'has-error' : '' }}">
                {!! Form::label('host_header',Lang::get('lang.host_header')) !!}
                {!! Form::text('host_header',null,['class' => 'form-control', 'placeholder' => Lang::get('lang.leave_blank')]) !!}
            </div>
            <div class="col-md-4 form-group {{ $errors->has('max_age') ? 'has-error' : '' }}">
                {!! Form::label('max_age',Lang::get('lang.max-age')) !!} <span>({{ Lang::get('lang.second') }})</span>
                <input name="max_age" type="text" value="{{ $resource->max_age }}" class="form-control" data-inputmask="'mask': '9', 'repeat': 10, 'greedy' : false" data-mask>
            </div>
        </div>
         <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('file_type') ? 'has-error' : '' }}">
                {!! Form::label('file_type',Lang::get('lang.file_type')) !!}<br>
                <input id="file_type" name="file_type" type="text" value="{{ $resource->file_type }}" class="form-control" data-role="tagsinput">
            </div>
        </div>
        @endif
        <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('organization') ? 'has-error' : '' }}">
                {!! Form::label('http', 'HTTP') !!}
                {!! Form::select('http',['1' => '1','2' => '2'],null,['class' => 'form-control']) !!}
                
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('ssl_cert') ? 'has-error' : '' }}">
                {!! Form::label('ssl_cert',Lang::get('lang.ssl_cert')) !!}         <span class="text-red"> * ({!! Lang::get('lang.pem_content') !!})</span>
                {!! Form::textarea('ssl_cert',null,['class' => 'form-control']) !!}
            </div>
            <div class="col-md-4 form-group {{ $errors->has('ssl_key') ? 'has-error' : '' }}">
                {!! Form::label('ssl_key',Lang::get('lang.ssl_private_key')) !!}         <span class="text-red"> * ({!! Lang::get('lang.pem_content') !!})</span>
                {!! Form::textarea('ssl_key',null,['class' => 'form-control']) !!}
            </div>
        </div>
        <div class="row">
            <div class="col-md-4 form-group {{ $errors->has('origin') ? 'has-error' : '' }}">
                {!! Form::label('origin',Lang::get('lang.origin')) !!}         <span class="text-red"> * ({!! Lang::get('lang.one_ip_per_line') !!})</span>
                {!! Form::textarea('origin',null,['class' => 'form-control']) !!}
            </div>
        </div>
        @if ($mode == 'edit')
            <div class="row">
                <div class="col-md-4 form-group">
                    {!! Form::label('created_at',Lang::get('lang.created')) !!}
                    <p>{{ $resource->created_at }}</p>
                </div>
                <div class="col-md-4 form-group">
                    <label>{!! Lang::get('lang.admin_panel') !!}</label>
                    <p>
                        <div class="input-group input-group-btn">
                            <label class="btn btn-sm btn-warning dropdown-toggle" data-toggle="dropdown">{{Lang::get('lang.action')}}
                                <span class="fa fa-caret-down"></span></label>
                            <ul class="dropdown-menu">
                                @if ($resource->status > 0 and (Auth::user()->role == "admin" or Auth::user()->role == "agent"))
                                    <li><a href="#" id="force_update_button">{{Lang::get('lang.force_update')}}</a></li>
                                @endif
                                
                                @if (!(($resource->status == 1 or ($resource->update_status > 0 && $resource->update_status < 3)) && $resource->error_msg == ''))
                                    @if ($resource->status > 0)
                                        <li><a href="#" id="dns_to_origin_button">{{Lang::get('lang.dns_to_origin')}}</a></li>
                                    @endif
                                    @if ($resource->status == -1)
                                        <li><a href="#" id="cancel_dns_to_origin_button">{{Lang::get('lang.cancel_dns_to_origin')}}</a></li>
                                    @endif
                                    @if ($resource->status <> 0)
                                        <li><a href="#" id="delete_button">{{Lang::get('lang.delete')}}</a></li>
                                    @endif
                                @endif
                                
                            </ul>
                        </div>
                        <script type="text/javascript">
                        
                        </script>
                    </p>
                </div>
                <div id="dg_confirm" class="modal fade">
                    <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                <h4 class="modal-title" id="dg_confirm_label">&nbsp;</h4>
                            </div>
                            <div class="modal-body">
                                {{Lang::get('lang.are_you_sure')}}?
                            </div>
                            <div class="modal-footer">
                                <button type="button" data-dismiss="modal" class="btn btn-primary" id="dg_confirm_bt">{{Lang::get('lang.delete')}}</button>
                                <button type="button" data-dismiss="modal" class="btn">{{Lang::get('lang.cancel')}}</button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>
    <div class="box-footer">
        @if ($mode == 'edit' and ($resource->status < 2 or ($resource->update_status > 0 && $resource->update_status < 3)) and $resource->error_msg == '')
            {!! Form::submit(Lang::get('lang.submit'),['class'=>'form-group btn btn-primary disabled'])!!}
        @else
            {!! Form::submit(Lang::get('lang.submit'),['class'=>'form-group btn btn-primary'])!!}
        @endif
    </div>
</div>
@stop

@section('FooterInclude')
        <script src="{{asset("lb-faveo/plugins/bootstrap-tagsinput/bootstrap-tagsinput.js")}}" type="text/javascript"></script>
        <script src="{{asset("lb-faveo/plugins/input-mask/jquery.inputmask.js")}}" type="text/javascript"></script>
    @if ($mode == 'edit')
        <script src="{{asset("lb-faveo/plugins/pace/pace.js")}}" type="text/javascript"></script>
    @endif

        <script type="text/javascript">
            $(function() {
                $("[data-mask]").inputmask();

                @if ($resource->status > 0 and (Auth::user()->role == "admin" or Auth::user()->role == "agent"))
                $('#force_update_button').on('click', function(){
                    $.post("{{url('resources-force-update')}}", {id:"{{ $resource->id }}"}, function (data) {
                        if (data.error) {
                            alert(data.error);
                        } else {
                            alert(data.msg);
                            location.reload();
                        }
                    });
                });
                @endif
                @if (!(($resource->status == 1 or ($resource->update_status > 0 && $resource->update_status < 3)) && $resource->error_msg == ''))
                    @if ($resource->status > 0)
                    $('#dns_to_origin_button').on('click', function(){
                        $('#dg_confirm_bt').html('{{Lang::get('lang.dns_to_origin')}}');
                        $('#dg_confirm').modal({
                            backdrop: 'static',
                            keyboard: false
                        })
                        .one('click', '#dg_confirm_bt', function(e) {
                            Pace.track(function(){
                            $.ajax({
                                type: 'POST',
                                dataType: 'json',
                                url: "{!! route('resource.dns-to-origin', $resource->id) !!}",
                                success: function (data) {
                                    if (data.error) {
                                        alert(data.error);
                                    } else {
                                        alert('{{Lang::get('lang.success')}}');
                                        window.location.href = "{!! route('resource.index') !!}";
                                    }
                                }
                            });
                            });
                        });
                    });
                    @endif
                    @if ($resource->status == -1)
                    $('#cancel_dns_to_origin_button').on('click', function(){
                        $('#dg_confirm_bt').html('{{Lang::get('lang.cancel_dns_to_origin')}}');
                        $('#dg_confirm').modal({
                            backdrop: 'static',
                            keyboard: false
                        })
                        .one('click', '#dg_confirm_bt', function(e) {
                            Pace.track(function(){
                            $.ajax({
                                type: 'POST',
                                dataType: 'json',
                                url: "{!! route('resource.cancel-dns-to-origin', $resource->id) !!}",
                                success: function (data) {
                                    if (data.error) {
                                        alert(data.error);
                                    } else {
                                        alert('{{Lang::get('lang.success')}}');
                                        window.location.href = "{!! route('resource.index') !!}";
                                    }
                                }
                            });
                            });
                        });
                    });
                    @endif
                    @if ($resource->status <> 0)
                    $('#delete_button').on('click', function(){
                        $('#dg_confirm_bt').html('{{Lang::get('lang.delete')}}');
                        $('#dg_confirm').modal({
                            backdrop: 'static',
                            keyboard: false
                        })
                        .one('click', '#dg_confirm_bt', function(e) {
                            Pace.track(function(){
                            $.ajax({
                                type: 'POST',
                                dataType: 'json',
                                data: {
                                    _method: 'DELETE'
                                },
                                url: "{!! route('resource.destroy', $resource->id) !!}",
                                success: function (data) {
                                    if (data.error) {
                                        alert(data.error);
                                    } else {
                                        alert('{{Lang::get('lang.delete_successfully')}}');
                                        window.location.href = "{!! route('resource.index') !!}";
                                    }
                                }
                            });
                            });
                        });
                    });
                    @endif
                @endif
            });
        </script>

@stop