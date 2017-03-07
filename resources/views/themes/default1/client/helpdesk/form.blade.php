@extends('themes.default1.client.layout.dashboard')

@section('title')
{!! Lang::get('lang.submit_a_ticket') !!} -
@stop

@section('Tickets')
class="active"
@stop

@section('ticket-bar')
active
@stop

@section('newticket')
class="active"
@stop

@section('PageHeader')
<h1>{{Lang::get('lang.create_ticket')}}</h1>
@stop

<!-- content -->
@section('content')
    @if(Session::has('message'))
    <div class="alert alert-success alert-dismissable">
        <i class="fa  fa-check-circle"></i>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        {!! Session::get('message') !!}
    </div>
    @endif
    @if (count($errors) > 0)
    @if(Session::has('check'))
    <?php goto a; ?>
    @endif
    @if(!Session::has('error'))
    <div class="alert alert-danger alert-dismissable">
        <i class="fa fa-ban"></i>
        <b>{!! Lang::get('lang.alert') !!} !</b>
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <ul>
            @foreach ($errors->all() as $error)
            <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
    @endif
    <?php a: ?>
    @endif
    <!-- open a form -->
    {{-- <script src="http://ajax.googleapis.com/ajax/libs/jquery/2.0.2/jquery.min.js"></script> --}}
    <script src="{{asset("lb-faveo/js/jquery2.0.2.min.js")}}" type="text/javascript"></script>
    <!--
    |====================================================
    | SELECT FROM
    |====================================================
    -->
    <?php
    $encrypter = app('Illuminate\Encryption\Encrypter');
    $encrypted_token = $encrypter->encrypt(csrf_token());
    ?>
    <input id="token" type="hidden" value="{{$encrypted_token}}">
    {!! Form::open(['action'=>'Client\helpdesk\FormController@postedForm','method'=>'post', 'enctype'=>'multipart/form-data']) !!}
    <div class="row">
        <div class="row col-md-12">
            
                @if(Auth::user())
                    
                        {!! Form::hidden('Name',Auth::user()->user_name,['class' => 'form-control']) !!}
                    
                @else
                    <div class="col-md-12 form-group {{ $errors->has('Name') ? 'has-error' : '' }}">
                        {!! Form::label('Name',Lang::get('lang.name')) !!}<span class="text-red"> *</span>
                        {!! Form::text('Name',null,['class' => 'form-control']) !!}
                    </div>
                @endif
            
            

                @if(Auth::user())
                    
                        {!! Form::hidden('Email',Auth::user()->email,['class' => 'form-control']) !!}
                    
                @else
                    <div class="col-md-12 form-group {{ $errors->has('Email') ? 'has-error' : '' }}">
                        {!! Form::label('Email',Lang::get('lang.email')) !!}
                        @if($email_mandatory->status == 1 || $email_mandatory->status == '1')
                        <span class="text-red"> *</span>
                        @endif
                        {!! Form::email('Email',null,['class' => 'form-control']) !!}
                    </div>
                @endif


                
            
                @if(!Auth::user())
                    
            <div class="col-md-2 form-group {{ Session::has('country_code_error') ? 'has-error' : '' }}">
                {!! Form::label('Code',Lang::get('lang.country-code')) !!}
                 @if($email_mandatory->status == 0 || $email_mandatory->status == '0')
                        <span class="text-red"> *</span>
                        @endif

                {!! Form::text('Code',null,['class' => 'form-control', 'placeholder' => $phonecode, 'title' => Lang::get('lang.enter-country-phone-code')]) !!}
            </div>
            <div class="col-md-5 form-group {{ $errors->has('mobile') ? 'has-error' : '' }}">
                {!! Form::label('mobile',Lang::get('lang.mobile_number')) !!}
                 @if($email_mandatory->status == 0 || $email_mandatory->status == '0')
                        <span class="text-red"> *</span>
                        @endif
                {!! Form::text('mobile',null,['class' => 'form-control']) !!}
            </div>
            <div class="col-md-5 form-group {{ $errors->has('Phone') ? 'has-error' : '' }}">
                {!! Form::label('Phone',Lang::get('lang.phone')) !!}
                {!! Form::text('Phone',null,['class' => 'form-control']) !!}
            </div>
            @else 
                {!! Form::hidden('mobile',Auth::user()->mobile,['class' => 'form-control']) !!}
                {!! Form::hidden('Code',Auth::user()->country_code,['class' => 'form-control']) !!}
                {!! Form::hidden('Phone',Auth::user()->phone_number,['class' => 'form-control']) !!}
 
           @endif
            <div class="col-md-12 form-group {{ $errors->has('help_topic') ? 'has-error' : '' }}">
                {!! Form::label('help_topic', Lang::get('lang.choose_a_help_topic')) !!} 
                {!! $errors->first('help_topic', '<spam class="help-block">:message</spam>') !!}
                <?php
                $forms = App\Model\helpdesk\Form\Forms::get();
                $helptopic = App\Model\helpdesk\Manage\Help_topic::where('status', '=', 1)->get();
                ?>                  
                <select name="helptopic" class="form-control" id="selectid">
                    
                    @foreach($helptopic as $topic)
                    <option value="{!! $topic->id !!}">{!! $topic->topic !!}</option>
                    @endforeach
                </select>
            </div>
            <!-- priority -->
             <?php 
             $Priority = App\Model\helpdesk\Settings\CommonSettings::select('status')->where('option_name','=', 'user_priority')->first(); 
             $user_Priority=$Priority->status;
            ?>
             
             @if(Auth::user())

             @if(Auth::user()->active == 1)
            @if($user_Priority == 1)
             

             <div class="col-md-12 form-group">
                <div class="row">
                    <div class="col-md-1">
                        <label>{!! Lang::get('lang.priority') !!}:</label>
                    </div>
                    <div class="col-md-12">
                        <?php $Priority = App\Model\helpdesk\Ticket\Ticket_Priority::where('status','=',1)->get(); ?>
                        {!! Form::select('priority', ['Priority'=>$Priority->lists('priority_desc','priority_id')->toArray()],null,['class' => 'form-control select']) !!}
                    </div>
                 </div>
            </div>
           @endif
            @endif
            @endif
            <div class="col-md-12 form-group {{ $errors->has('Subject') ? 'has-error' : '' }}">
                {!! Form::label('Subject',Lang::get('lang.subject')) !!}<span class="text-red"> *</span>
                {!! Form::text('Subject',null,['class' => 'form-control']) !!}
            </div>
            <div class="col-md-12 form-group {{ $errors->has('Details') ? 'has-error' : '' }}">
                {!! Form::label('Details',Lang::get('lang.message')) !!}<span class="text-red"> *</span>
                {!! Form::textarea('Details',null,['class' => 'form-control']) !!}
            </div>
            <div class="col-md-12 form-group">
                <div class="btn btn-default btn-file"><i class="fa fa-paperclip"> </i> {!! Lang::get('lang.attachment') !!}<input type="file" name="attachment[]" multiple/></div><br/>
                {!! Lang::get('lang.max') !!}. 10MB
            </div>
            {{-- Event fire --}}
            <?php Event::fire(new App\Events\ClientTicketForm()); ?>
            <div class="col-md-12" id="response"> </div>
            <div id="ss" class="xs-md-6 form-group {{ $errors->has('') ? 'has-error' : '' }}"> </div>
            <div class="col-md-12 form-group">{!! Form::submit(Lang::get('lang.Send'),['class'=>'form-group btn btn-info pull-left', 'onclick' => 'this.disabled=true;this.value="Sending, please wait...";this.form.submit();'])!!}</div>
        </div>
        <div class="col-md-12" id="response"> </div>
        <div id="ss" class="xs-md-6 form-group {{ $errors->has('') ? 'has-error' : '' }}"> </div>
    </div>
    {!! Form::close() !!}
<!--
|====================================================
| SELECTED FORM STORED IN SCRIPT
|====================================================
-->
<script type="text/javascript">
$(document).ready(function(){
   var helpTopic = $("#selectid").val();
   send(helpTopic);
   $("#selectid").on("change",function(){
       helpTopic = $("#selectid").val();
       send(helpTopic);
   });
   function send(helpTopic){
       $.ajax({
           url:"{{url('/get-helptopic-form')}}",
           data:{'helptopic':helpTopic},
           type:"GET",
           dataType:"html",
           success:function(response){
               $("#response").html(response);
           },
           error:function(response){
              $("#response").html(response); 
           }
       });
   }
});

$(function() {
//Add text editor
    $("textarea").wysihtml5();
});
</script>
@stop
