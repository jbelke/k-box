
<div id="document-tree" class="tree-view">
	
	@if($user_can_edit_personal_groups)

	<div class="tree-header">
	
		<strong><span class="icon icon-action-black icon-action-black-ic_label_black_24dp"></span>{{trans('groups.collections.personal_title')}}</strong>
	
		<div class="u-pull-right magic">
			@if($user_can_edit_personal_groups || $user_can_see_private_groups)
			<a href="#" rv-on-click="menu.createGroup" data-isprivate="true" title="{{trans('actions.create_collection_btn')}}">
				<span class="btn-icon icon-content-black icon-content-black-ic_add_circle_outline_black_24dp"></span>
			</a>
			@endif 
			
			<!--<a href="#" rv-on-click="groups.expandOrCollapseAll" data-expanded="{{trans('actions.collapse_all')}}" data-collapsed="{{trans('actions.expand_all')}}">
				{{trans('actions.expand_all')}}
			</a>-->
		</div>
	
	</div>

	<div class="tree-group">
			
		<div class="elements">

			<ul class="clean-ul">
			
				@forelse($personal_groups as $group)
			
					@include('groups.tree-item')
			
				@empty
					
					<p class="description">{{trans('groups.collections.description')}}</p>
						
					@if($user_can_edit_personal_groups)
					<a href="#" rv-on-click="menu.createGroup" data-isprivate="true" title="{{trans('actions.create_collection_btn')}}" class="button">
						<span class="btn-icon icon-content-black icon-content-black-ic_add_circle_outline_black_24dp"></span> {{trans('actions.create_collection_btn')}}
					</a>
					@endif
			
				@endforelse
			
			</ul>
		</div>
	</div>

	@endif

	@if($user_can_see_private_groups)
	
	<div class="tree-header">
	
		<strong><span class="icon icon-action-black icon-action-black-ic_label_outline_black_24dp"></span>{{trans('groups.collections.private_title')}}</strong>
	
		<div class="u-pull-right magic">
			@if($user_can_edit_private_groups)
			<a href="#" rv-on-click="menu.createGroup"  data-isprivate="false" title="{{trans('actions.create_collection_btn')}}">
				<span class="btn-icon icon-content-black icon-content-black-ic_add_circle_outline_black_24dp"></span>
			</a>
			@endif 
			
			<!--<a href="#" rv-on-click="groups.expandOrCollapseAll" data-expanded="{{trans('actions.collapse_all')}}" data-collapsed="{{trans('actions.expand_all')}}">
				{{trans('actions.expand_all')}}
			</a>-->

		</div>
	
	</div>

	<div class="tree-group">
			
		<div class="elements">
			
			<ul class="clean-ul">
			
			@forelse($private_groups as $group)
		
				@include('groups.tree-item')
				
			@empty
			
				<p>No Institutions collections available</p>
				
			@endforelse

			</ul>
		</div>
	</div>
	@endif

</div>