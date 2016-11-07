<?php

    $vars = Session::all();
    foreach ($vars as $key => $value) {
        switch($key) {
            case 'success':
            case 'error':
            case 'warning':
            case 'info':
                ?>
                <div class="row">
                    <div class="alert alert-{{ ($key == 'error') ? 'danger' : $key }} alert-dismissable">
                        <button type="button" class="close" data-dismiss="alert" aria-hidden="true">&times;</button>
                        <strong>{{ ucfirst($key) }}:</strong> {!! $value !!}
                    </div>
                </div>
                <?php
                Session::forget($key);
                break;
            default:
        }
    }

?>
