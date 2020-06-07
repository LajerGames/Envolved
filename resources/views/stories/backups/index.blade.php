@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Backups for: {{$info['story']->title}}</div>
            <div class="panel-body">

                <table class="table">
                    <tr>
                        <th scope="col" class="icon"></th>
                        <th scope="col">Name</th>
                        <th scope="col">Created at</th>
                        <th scope="col" class="icon text-center"><a href="javascript:void(0);" id="backup" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span></a></th>
                    </tr>
                    @if(count($info['backups']) > 0)
                        @foreach($info['backups'] as $backup)
                            <tr>
                                <td scope="col">
                                    {!!Form::open([
                                        'action' => ['BackupController@destroy', $info['story']->id, $backup->id],
                                        'method' => 'post'
                                    ])!!}
                                    {{Form::hidden('_method', 'DELETE')}}
                                    {{Form::submit('Delete', ['class' => 'btn btn-danger btn-delete'])}}
                                    {!!Form::close()!!}
                                </td>
                                <td scope="col">{{$backup->backup_name}}</td>
                                <td scope="col">{{$backup->updated_at}}</td>
                                <td scope="col">
                                    {!!Form::open([
                                        'action' => ['BackupController@implement', $info['story']->id, $backup->id],
                                        'method' => 'post'
                                    ])!!}
                                    {{Form::hidden('_method', 'post')}}
                                    {{Form::submit('Implement', ['class' => 'btn btn-primary btn-implement-backup'])}}
                                    {!!Form::close()!!}
                            </tr>
                        @endforeach
                    @else
                        <tr>
                            <td colspan="4" class="text-center">No backups found</td>
                        </tr>
                    @endif
                </table>

            </div>
        </div>
    </div>
</div>
@endsection