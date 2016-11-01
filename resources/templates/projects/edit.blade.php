@extends('management-layout')



@section('sub-header')

	

		<a href="{{route('projects.index')}}" class="parent">{{trans('projects.page_title')}}</a> {{trans('projects.edit_page_title', ['name' => $project->name])}}


@stop


@section('action-menu')
	
	<div class="action-group">

		<a href="{{route('projects.show', ['id' => $project->id])}}" class="button">
			<span class="btn-icon icon-content-white icon-content-white-ic_create_white_24dp"></span>{{trans('projects.close_edit_button')}}
		</a>

	</div>
	
@stop



@section('content')


    <h3>{{trans('projects.edit_page_title', ['name' => $project->name])}}</h3>
	
	
	@include('errors.list')


    <form  method="post" class="js-project-form" action="{{route('projects.update', ['id' => $project->id])}}">
		
		<input type="hidden" name="_method" value="PUT">
		
		@include('projects.partials.form', ['submit_btn' => trans('projects.labels.edit_submit'), 'cancel_route' => route('projects.show', $project->id)])
    
    </form>
			
			


@stop





@section('panels')

@include('panels.generic')

@stop

@section('scripts')

	<script>

	// require(['jquery'], function($){

	// 	$(".js-select-users").select2({
	// 		placeholder: "{{trans('projects.labels.users_placeholder')}}",
	// 	});

	// });

	</script>

@stop