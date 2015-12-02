<?php namespace KlinkDMS\Http\Composers;


use KlinkDMS\Capability;
use KlinkDMS\DocumentDescriptor;
use KlinkDMS\File;

use Illuminate\Contracts\View\View;

use Illuminate\Contracts\Auth\Guard as AuthGuard;

use Illuminate\Support\Collection;

class DocumentsComposer {

    /**
     * ...
     *
     * @var 
     */
    protected $adapter;

    /**
     * [$documents description]
     * @var \Klink\DmsDocuments\DocumentsService
     */
    private $documents = NULL;
    
    

    /**
     * Create a new profile composer.
     *
     * @param  UserRepository  $users
     * @return void
     */
    public function __construct(\Klink\DmsAdapter\KlinkAdapter $adapter, \Klink\DmsDocuments\DocumentsService $documentsService)
    {
        
        $this->adapter = $adapter;

        $this->documents = $documentsService;
    }


    public function layout(View $view)
    {
        if(\Auth::check()){

            $auth_user = \Auth::user();

            $view->with('can_import', $auth_user->can(Capability::IMPORT_DOCUMENTS));

            $view->with('can_upload', $auth_user->can(Capability::UPLOAD_DOCUMENTS));
            
            $view->with('can_create_collection', $auth_user->can(Capability::MANAGE_OWN_GROUPS) || $auth_user->can(Capability::MANAGE_INSTITUTION_GROUPS));
            
            $view->with('can_make_public', $auth_user->can(Capability::CHANGE_DOCUMENT_VISIBILITY));
            
            $view->with('can_clean_trash', $auth_user->can(Capability::CLEAN_TRASH));
            
            $view->with('can_share', $auth_user->can(array(Capability::SHARE_WITH_PERSONAL, Capability::SHARE_WITH_PRIVATE)));

            $view->with('can_manage_documents', $auth_user->isContentManager());

            $view->with('list_style_current', $auth_user->optionListStyle());
        }
        else {
            $view->with('list_style_current', 'tiles');
        }
    }

    /**
     * Document Descriptor page with actual DocumentDescriptor instance
     *
     * @param  View  $view
     * @return void
     */
    public function descriptor(View $view)
    {

        // dd($view['document']);

        $docOrItem = isset($view['item']) ? $view['item'] : (isset($view['document']) ? $view['document'] : null);

        if(is_array($docOrItem) && isset($docOrItem['descriptor'])){
            $docOrItem = $docOrItem['descriptor'];
            if(isset($view['item'])) {
                $view->with('item', $docOrItem);
            }
            else if(isset($view['document'])) {
                 $view->with('document', $docOrItem);
            }
        }

        if(\Auth::check() && !is_null($docOrItem)){
// dd($docOrItem);
            if(class_basename(get_class($docOrItem)) === 'DocumentDescriptor'){

                $document = $docOrItem;

                $auth_user = \Auth::user();


                $view->with('badge_private', $document->isPrivate());

                $view->with('is_starrable', true);

                if($document->isStarred($auth_user->id)){

                    $view->with('is_starred', true);

                    $star = $document->getStar($auth_user->id);

                    $view->with('star_id', $star->id);

                }
                else {
                    $view->with('is_starred', false);
                }

                $view->with('badge_shared', $document->isShared());

                if($auth_user->can(Capability::EDIT_DOCUMENT)){

                    $view->with('badge_error', $document->status === DocumentDescriptor::STATUS_ERROR);

                }
                else {
                    $view->with('badge_error', false);
                }

            }

        }

    }


    public function descriptorPanel(View $view){

        $docOrItem = isset($view['item']) ? $view['item'] : (isset($view['document']) ? $view['document'] : null);

        $this->descriptor($view);
        
        $auth_check = \Auth::check();
        
        
        $view->with('is_user_logged', $auth_check);
        
        if(!is_null($docOrItem) && is_array($docOrItem) && isset($docOrItem['descriptor'])){
            $docOrItem = $docOrItem['descriptor'];
            if(isset($view['item'])) {
                $view->with('item', $docOrItem);
            }
            else if(isset($view['document'])) {
                 $view->with('document', $docOrItem);
            }
        }

        if($auth_check && !is_null($docOrItem)){

            if(class_basename(get_class($docOrItem)) === 'DocumentDescriptor'){

                $document = $docOrItem;

                $auth_user = \Auth::user();

                $view->with('stars_count', $document->stars()->count());

                if($document->isMine() && $document->groups()->private($auth_user->id)->orPublic()->count() > 0){
                    $view->with('is_in_collection', true);

                    //is_private

                    $view->with('groups', $document->groups()->private($auth_user->id)->orPublic()->get());

                    $view->with('user_can_edit_private_groups', $auth_user->can(Capability::MANAGE_OWN_GROUPS));
                    $view->with('user_can_edit_public_groups', $auth_user->can(Capability::MANAGE_INSTITUTION_GROUPS));

                }
                else {
                    $view->with('is_in_collection', false);
                }

                // $view->with('badge_shared', $document->isShared());

                // the document is shared with me
                // $with_me = $document->shares()->sharedWithMe($auth_user)->with('user')->get();
                
                if($document->isMine()){
                // the document is shared by me
                    $by_me = $document->shares()->by($auth_user)->with('sharedwith')->get();
    
    
                    // $view->with('shared_with_me', $with_me);
                    $view->with('shared_by_me', $by_me);
                
                }

                // dd(compact('with_me', 'by_me'));
                
                if($auth_user->can(Capability::EDIT_DOCUMENT)){
                    $view->with('user_can_edit', true);
                }
                else {
                    $view->with('user_can_edit', false);
                }
                
                
                $view->with('use_groups_page', $auth_user->can(Capability::MANAGE_OWN_GROUPS));


                if($auth_user->can(Capability::UPLOAD_DOCUMENTS) && !is_null($document->file)){
                    $view->with('show_versions', true);

                    $view->with('has_versions', !is_null($document->file->revision_of));


                    // dd(['file' => $document->file()->first(), 
                    //     'revision_of' => $document->file->revisionOf()->get(),
                    //     'versions' => $document->file->revisionOfRecursive()->get()->toArray(),
                    //     // 'from' => $document->file()->revision_of
                    //    ]);


                }
                else {
                    $view->with('show_versions', false);
                }

            }

            
            // 
            // get info about sharing

            // if($auth->check()){


        //  $view_params['is_shareable'] = true;



        //  $view_params['share_info'] = 'shared_with or shared_by';

        //  // shared with
        //  // shared by
        //  // insert also details about the sharing

        //  $view_params['share_info'] = ''
        // }
            // 
            // get info about groups

        }

    }



    public static function _flatten_revisions(File $file, &$revisions = array()){

        $revisions[] = $file;

        if(is_null($file->revision_of)){
            return $revisions;
        }
        else {
            return self::_flatten_revisions($file->revisionOf()->first(), $revisions);
        }
    }


    public function versionInfo(View $view)
    {
        $docOrItem = isset($view['item']) ? $view['item'] : isset($view['document']) ? $view['document'] : null;

        if(is_array($docOrItem) && isset($docOrItem['descriptor'])){
            $docOrItem = $docOrItem['descriptor'];
            if(isset($view['item'])) {
                $view->with('item', $docOrItem);
            }
            else if(isset($view['document'])) {
                 $view->with('document', $docOrItem);
            }
        }

        if(\Auth::check() && !is_null($docOrItem)){

            if(class_basename(get_class($docOrItem)) === 'DocumentDescriptor'){

                $document = $docOrItem;

                $auth_user = \Auth::user();



                if($auth_user->can(Capability::UPLOAD_DOCUMENTS) && !is_null($document->file)){
                    
                    // $view->with('show_versions', true);

                    $view->with('has_versions', !is_null($document->file->revision_of));

                    $alls = self::_flatten_revisions($document->file);

                    $view->with('versions_count', count($alls));
                    $view->with('versions', $alls);


                }
                else {
                    $view->with('versions_count', 0);
                    $view->with('versions', array());
                }

            }

        }
    }
    
    
    public function preview(View $view){
//        $body_classes = isset($view['body_classes']) ? $view['body_classes'] : array();
        
        //dd($body_classes);
        
//        $view->with('body_classes', implode(' ', $body_classes));

        $view->with('is_user_logged', \Auth::check());
    }


    public function facets(View $view){
        
        $auth_user = \Auth::user();

        $facets = isset($view['facets']) ? $view['facets'] : null;
        $filters = isset($view['filters']) ? $view['filters'] : null;
        $current_visibility = isset($view['current_visibility']) ? $view['current_visibility'] : 'private';
        $are_filters_empty = empty($filters);
        
        if($current_visibility=='private'){
            $cols = array(              
                'language' => array('label' => trans('search.facets.language')),
                'documentType' => array('label' => trans('search.facets.documentType')),
            );    
        }
        else {
            $cols = array(
                'institutionId' => array('label' => trans('search.facets.institutionId')),
                'language' => array('label' => trans('search.facets.language')),
                'documentType' => array('label' => trans('search.facets.documentType')),
            );
            
        }
        
        
        if(!is_null($facets)){
            
            
            
            
            $group_facets = array_values(array_filter($facets, function($f){
                return $f->name === 'documentGroups';
            }));
            
            if(!empty($group_facets)){
                $private = array();
                $personal = array();
                
                $items = $group_facets[0]->items;
                
                foreach($items as $group_facet){
                    
                    try{
                    
                    if($group_facet->count > 0){
                        if(starts_with($group_facet->term, '0:')){
                            // private
                            $pg = \KlinkDMS\Group::findOrFail(str_replace('0:', '', $group_facet->term));
                            $group_facet->label = $pg->name;
                            $group_facet->selected = false;
                            $group_facet->collapsed = $group_facet->count == 0;
                            $group_facet->institution = true;
                            $private[] = $group_facet;
                            
                        }
                        else if(starts_with($group_facet->term, $auth_user->id . ':')){
                            //personal
                            $pug = \KlinkDMS\Group::findOrFail(str_replace($auth_user->id . ':', '', $group_facet->term));
                            $group_facet->label = $pug->name;
                            $group_facet->selected = false;
                            $group_facet->collapsed = $group_facet->count == 0;
                            $private[] = $group_facet;
                            //dd($group_facet);
                        }
                        else if(strpos($group_facet->term, ':')){
                            // dd($group_facet);
                            // $group_facet->label = '';
                            // $group_facet->selected = false;
                            // $group_facet->collapsed = true;
                            //$private[] = $group_facet;

                        }

                    }
                    
                    }catch(\Exception $exc){
                        
                    }
                    
                }


                
                $cols['documentGroups'] = array('label' => 'Collections', 'items' => $private);
                
            }
            
            foreach($facets as $f){
                if(array_key_exists($f->name, $cols)){
                    
                    $cols[$f->name]['items'] = array_filter(array_map(function($f_items) use($f, $filters, $are_filters_empty) {
                        
                        if(!$are_filters_empty){
                        
                            if(array_key_exists($f->name, $filters) && in_array($f_items->term, $filters[$f->name])){
                                $f_items->selected = true;
                                $f_items->collapsed = $f_items->count == 0;
                            }
                            else if(array_key_exists($f->name, $filters)){
                                $f_items->selected = false;
                                $f_items->collapsed = $f_items->count == 0;
                            }
                            else {
                                $f_items->selected = false;
                                $f_items->collapsed = $f_items->count == 0;
                            }
                            
                        }
                        else {
                            $f_items->selected = false;
                            $f_items->collapsed = $f_items->count == 0;
                        }

                        if($f->name==='documentGroups' && !property_exists($f_items, 'label')){
                            return false;
                        }

                        return $f_items;
                        
                    }, $f->items));
                }
            }
            
            
        }
// dd($cols);
        $view->with('columns', $cols);
        
        $view->with('width', 100/count($cols));
        
        
        $current_visibility = isset($view['current_visibility']) ? $view['current_visibility'] : 'private';
        
        $search_terms =  isset($view['search_terms']) && !empty($view['search_terms']) ? $view['search_terms'] : '*';
        
        //include active facets/filters
        
        $b_url = '?s=' . $search_terms . '&visibility=' . $current_visibility;
        $url = $b_url;
        
        // if(!$are_filters_empty){
        //     $fs = array_keys($filters);
        //     
        //     $active = array();
        //     
        //     foreach($filters as $key => $values){
        //                         
        //         $active[] = $key.'='.implode(',', $values);
        //         
        //     }
        //     
        //     $url = $b_url . '&fs=' . implode(',', $fs).'&' . implode('&', $active);
        // }
        

        // $view->with('facet_search_base_url', $url);
        
        $view->with('facet_filters_url', $b_url);
        
        $view->with('current_active_filters', $filters);



    }
    
    public function groupFacets(View $view){
        
        $auth_user = \Auth::user();
        
        $facets = isset($view['facets']) ? $view['facets'] : null;
        
        
        if(!is_null($facets)){
            
            $group_facets = array_values(array_filter($facets, function($f){
                return $f->name === 'documentGroups';
            }));
            
            $private = array();
            $personal = array();
            
            $items = $group_facets[0]->items;
            
            foreach($items as $group_facet){
                
                if($group_facet->count > 0){
                    if(starts_with($group_facet->term, '0:')){
                        // private
                        $private[] = \KlinkDMS\Group::findOrFail(str_replace('0:', '', $group_facet->term));
                        
                    }
                    else if(starts_with($group_facet->term, $auth_user->id . ':')){
                        //personal
                        $personal[] = $group_facet;
                    }
                }
                
                
            }
            
//            dd(compact('private', 'personal'));
            
//            foreach($facets as $f){
//                if(array_key_exists($f->name, $cols)){
//                    $cols[$f->name]['items'] = $f->items;
//                }
//            }
            
            $view->with('facets_groups_personal', $personal);
        
            $view->with('facets_groups_private', $private);
            
        }

//        $view->with('faceted_groups', $cols);
        
//        $view->with('width', 100/4);

    }
    
    
     public function shared(View $view)
    {
        if(\Auth::check()){

            $auth_user = \Auth::user();

            $view->with('can_share_with_personal', $auth_user->can(Capability::SHARE_WITH_PERSONAL));

            $view->with('can_share_with_private', $auth_user->can(Capability::SHARE_WITH_PRIVATE));
            
            $view->with('can_see_share', $auth_user->can(Capability::RECEIVE_AND_SEE_SHARE));
            
            

            $view->with('list_style_current', $auth_user->optionListStyle());
        }
        else {
            $view->with('list_style_current', 'tiles');
        }
    }
    
}