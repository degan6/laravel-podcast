@extends('layouts.app')

@section('template_title')
    Listen
@endsection

@section('content')
    @if($podcast_items)
        @include('podcasts.player')
    @endif
    <div class="container container-podcast-list">
        <div class="row">
            <div class="col-md-10 col-md-offset-1">
                <h3 class="page-title">
                    {{ $podcast->name }}

                    <span class="pull-right text-muted">
                        {{ $podcast->count() }} Episodes
                    </span>
                </h3>
                <p>
                    {{ $podcast->description }}
                </p>
                <div class="podcast-action-list">
                    <ul class="list-inline">
                        <li><a href="{{ $podcast->web_url }}">Website</a></li>
                        <li class='feed-delete pull-right' data-toggle="tooltip" data-placement="top" title="Delete podcast feed">
                            {!! Form::open(array('url' => 'podcasts/' . $podcast->id)) !!}
                            {!! Form::hidden('_method', 'DELETE') !!}
                            {!! Form::button('<i class="fa fa-remove fa-fw" aria-hidden="true"></i> Delete', array('class' => 'btn btn-delete','type' => 'button', 'data-toggle' => 'modal', 'data-target' => '#confirmDelete', 'data-title' => 'Delete Podcast', 'data-message' => 'Are you sure you want to delete this podcast ?')) !!}
                            {!! Form::close() !!}
                        </li>
                    </ul>
                </div>
                <hr/>
            </div>
        </div>
        <div class="row">
            <div class="col-md-8 col-md-offset-2">
                @if($podcast_items)
                    @foreach ($podcast_items as $item)
                        @include('podcasts.item')
                    @endforeach
                    <div class="row container-fluid">
                        {{ $podcast_items->render() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
    @include('modals.modal-delete')
@endsection

@section('footer-scripts')
    @include('scripts.podcast-scripts')
    @include('scripts.delete-modal-script')
@endsection
