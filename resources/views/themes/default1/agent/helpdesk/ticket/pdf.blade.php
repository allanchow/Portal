<!DOCTYPE html>
<html>
    <head>
        <title>PDF</title>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body { font-family: SimHei; }
        </style>
    </head>
    <body>
<h3>Ticket Title : {!! $tickets->title !!}</h3><br>
<h3>Ticket Number : {!! $tickets->ticket_number !!}</h3><br>
<h3>Ticket Department : {!! $tickets->department !!}</h3><br>
<h3>Ticket Helptopic : {!! $tickets->helptopic !!}</h3><br>
@forelse($ticket->thread as $thread)
{!! $thread->body !!}
<hr>
@empty 

@endforelse
    </body>
</html>
