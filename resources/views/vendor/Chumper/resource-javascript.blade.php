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
                "bProcessing": true,
                "bServerSide": true,
                "order": [[3, "desc"]],
                "pageLength": 50,
                "ajax": {
                    url: "{{url('resource-list')}}",
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

        $('.pending').on('click', function(){
            show = 'pending';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.suspended').on('click', function(){
            show = 'suspended';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.deleting').on('click', function(){
            show = 'deleting';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('.revert-dns').on('click', function(){
            show = 'revert-dns';
            classname = '.'+show;
            filterTable(show);
            toggleActiveClass(classname);
        });

        $('#force_update_button').on('click', function(){
            $.post("{{url('resources-force-update')}}", function (data) {
                if (data.error) {
                    alert(data.error);
                } else {
                    alert(data.msg);
                    filterTable(show);
                }
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
    });
</script>
