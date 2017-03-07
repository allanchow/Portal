                    @if(Session::has('success'))
                    <div class="alert alert-success alert-dismissable">
                        <i class="fa  fa-check-circle"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{Session::get('success')}}
                    </div>
                    @endif
                    @if(Session::has('warning'))
                    <div class="alert alert-warning alert-dismissable">
                        <i class="fa  fa-check-circle"></i>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {!! Session::get('warning') !!}
                    </div>
                    @endif
                    <!-- failure message -->
                    @if(Session::has('fails'))
                    @if(Session::has('check'))
                    <?php goto a; ?>
                    @endif
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>{!! Lang::get('lang.alert') !!} !</b>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        {{Session::get('fails')}}
                    </div>
                    <?php a: ?>
                    @endif
                    @if(Session::has('errors'))
                    <div class="alert alert-danger alert-dismissable">
                        <i class="fa fa-ban"></i>
                        <b>{!! Lang::get('lang.alert') !!}!</b>
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <br/>
                        @foreach ($errors->all() as $error)
                        <li class="error-message-padding">{!! $error !!}</li>
                        @endforeach
                    </div>
                    @endif
