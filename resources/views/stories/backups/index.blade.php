@extends('layouts.editor')

@section('content')
<div class="row">
    <div class="col-md-12">
        <div class="panel panel-default">
            <div class="panel-heading">Backups for:</div>
            <div class="panel-body">

                <table class="table">
                    <tr>
                        <th scope="col" class="icon"></th>
                        <th scope="col">Name</th>
                        <th scope="col">Created at</th>
                        <th scope="col" class="icon text-center"><a href="javascript:void(0);" id="backup" class="btn btn-success"><span class="glyphicon glyphicon-plus"></span></a></th>
                    </tr>
                </table>

            </div>
        </div>
    </div>
</div>
@endsection