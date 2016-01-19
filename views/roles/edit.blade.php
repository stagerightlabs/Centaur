@extends('Centaur::layout')

@section('title', 'Edit Role')

@section('content')
<div class="row">
    <div class="col-md-4 col-md-offset-4">
        <div class="panel panel-default">
            <div class="panel-heading">
                <h3 class="panel-title">Edit Role</h3>
            </div>
            <div class="panel-body">
                <form accept-charset="UTF-8" role="form" method="post" action="{{ route('roles.update', $role->id) }}">
                <fieldset>
                    <div class="form-group {{ ($errors->has('name')) ? 'has-error' : '' }}">
                        <input class="form-control" placeholder="Name" name="name" type="text" value="{{ $role->name }}" />
                        {!! ($errors->has('name') ? $errors->first('name', '<p class="text-danger">:message</p>') : '') !!}
                    </div>
                    <div class="form-group {{ ($errors->has('slug')) ? 'has-error' : '' }}">
                        <input class="form-control" placeholder="slug" name="slug" type="text" value="{{ $role->slug }}" />
                        {!! ($errors->has('slug') ? $errors->first('slug', '<p class="text-danger">:message</p>') : '') !!}
                    </div>

                    <h5>Permissions:</h5>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[users.create]" value="1" {{ $role->hasAccess('users.create') ? 'checked' : '' }}>
                            users.create
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[users.update]" value="1" {{ $role->hasAccess('users.update') ? 'checked' : '' }}>
                            users.update
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[users.view]" value="1" {{ $role->hasAccess('users.view') ? 'checked' : '' }}>
                            users.view
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[users.destroy]" value="1" {{ $role->hasAccess('users.destroy') ? 'checked' : '' }}>
                            users.destroy
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[roles.create]" value="1" {{ $role->hasAccess('roles.create') ? 'checked' : '' }}>
                            roles.create
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[roles.update]" value="1" {{ $role->hasAccess('roles.update') ? 'checked' : '' }}>
                            roles.update
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[roles.view]" value="1" {{ $role->hasAccess('roles.view') ? 'checked' : '' }}>
                            roles.view
                        </label>
                    </div>
                    <div class="checkbox">
                        <label>
                            <input type="checkbox" name="permissions[roles.delete]" value="1" {{ $role->hasAccess('roles.delete') ? 'checked' : '' }}>
                            roles.delete
                        </label>
                    </div>
                    <input name="_token" value="{{ csrf_token() }}" type="hidden">
                    <input name="_method" value="PUT" type="hidden">
                    <input class="btn btn-lg btn-primary btn-block" type="submit" value="Update">
                </fieldset>
                </form>
            </div>
        </div>
    </div>
</div>
@stop