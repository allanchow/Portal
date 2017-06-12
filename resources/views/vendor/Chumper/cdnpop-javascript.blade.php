<?php
$segments = \Request::segments();
$segment = "";
foreach($segments as $seg){
    $segment.="/".$seg;
}
?>
<script type="text/javascript">
    jQuery(document).ready(function () {
        var show = 'all';
        var searchTerm = '';
        oTable = myFunction(show, searchTerm);
        
        $("select[name=type_of_profile]").change(function () {
            //alert($('select[name=type_of_profile]').val());
            $("#chumper").dataTable().fnDestroy();
            myFunction();
        });

        function getStatus()
        {
            $.get("{{url('cdnpop-status')}}", function (data) {
                if (data.error) {
                    alert(data.error);
                } else {
                    if (data.msg == 'Normal') {
                        html = '<span class="label label-success">' + data.msg + '</span>';
                    } else {
                        html = '<span class="label label-danger">' + data.msg + '</span>';
                    }
                    $('#pop_status').html(html);
                    filterTable(show);
                }
            });
        }

        function myFunction(show)
        {
            return jQuery('#chumper').dataTable({
                "sDom": "<'row'<'col-xs-6'l><'col-xs-6'>r>"+
                        "t"+
                        "<'row'<'col-md-6'i><'col-md-6'p>>",
                "sPaginationType": "full_numbers",
                "oLanguage": {
                    "oPaginate": {
                        "sFirst": '&laquo;',
                        "sPrevious": '&lsaquo;',
                        "sNext": '&rsaquo;',
                        "sLast": '&raquo;'
                    }
                },
                "initComplete": function () {
                    $('.btn_change').each(function() {
                        $(this).on('click', function(event){
                            changeStatus($(this).attr('data'));
                        });
                    });
                },
                "bProcessing": true,
                "bServerSide": true,
                "order": [[0, "asc"]],
                "pageLength": 50,
                "ajax": {
                    url: "{{url('cdnpop-list')}}",
                    data: function (d) {
                        d.profiletype = show;
                        d.searchTerm = searchTerm;
                    }
                }
                
            });
        }

        $('.all').on('click', function(){
            show = 'all';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.active').on('click', function(){
            show = 'active';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.inactive').on('click', function(){
            show = 'inactive';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('#attacking').on('click', function(){
            Pace.track(function(){
                $.post("{{url('cdnpop-attack')}}", function (data) {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        alert('{{Lang::get('lang.updated_successfully')}}');
                        getStatus();
                    }
                });
            });
        });

        $('#resume').on('click', function(){
            Pace.track(function(){
                $.post("{{url('cdnpop-resume')}}", function (data) {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        alert('{{Lang::get('lang.updated_successfully')}}');
                        getStatus();
                    }
                });
            });
        });

        document.getElementById('search-text').onkeypress = function(e){
            if (!e) e = window.event;
            var keyCode = e.keyCode || e.which;
            if (keyCode == '13'){ 
                searchTerm = $('input[name=search]').val();
                $("#chumper").dataTable().fnDestroy();
                myFunction(show, searchTerm);
            }
        }

        function filterTable(show) {
            $("#chumper").dataTable().fnDestroy();
            myFunction(show, searchTerm);
        }

        function toggleActiveClass(classname) {
            $('.active').removeClass('active');
            $(classname).parent('li').addClass('active');
        }

        function changeStatus(pop_hostname) {
            Pace.track(function(){
                $.post("{{url('cdnpop-change')}}/"+pop_hostname, function (data) {
                    if (data.error) {
                        alert(data.error);
                    } else {
                        alert('{{Lang::get('lang.updated_successfully')}}');
                        filterTable(show);
                    }
                });
            });
        }

        getStatus();
    });
</script>
