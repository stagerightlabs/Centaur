@if ($message = Session::get('success'))
<div class="row">
    <div class="alert alert-success alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>Success:</strong> {!! $message !!}
    </div>
</div>
<?php Session::forget('success');?>
@endif

@if ($message = Session::get('error'))
<div class="row">
    <div class="alert alert-danger alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>Error:</strong> {!! $message !!}
    </div>
</div>
<?php Session::forget('error'); ?>
@endif

@if ($message = Session::get('warning'))
<div class="row">
    <div class="alert alert-warning alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>Warning:</strong> {!! $message !!}
    </div>
</div>
<?php Session::forget('warning'); ?>
@endif

@if ($message = Session::get('info'))
<div class="row">
    <div class="alert alert-info alert-dismissable">
        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
        <strong>FYI:</strong> {!! $message !!}
    </div>
</div>
<?php Session::forget('info'); ?>
@endif
