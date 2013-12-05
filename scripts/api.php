<?php
require_once dirname( dirname( __FILE__ ) ) . '/gp-load.php';

$project_path = 'osclass/dev';

$gp_prj = new GP_Project();
$project = $gp_prj->by_path($project_path);
$tree = projectTree($project->id);

$locales = array();
foreach($tree as $id) {
    $translation_sets = GP::$translation_set->by_project_id( $id );
    foreach( $translation_sets as $set ) {
        $locale = $set->locale;
        $locales[$locale]['locale'] = $locale;
        $locales[$locale]['name'] = $set->name_with_locale();
        $locales[$locale]['current'] = @$locales[$locale]['current']+$set->current_count();
        $locales[$locale]['all'] = @$locales[$locale]['all']+$set->all_count();
        $locales[$locale]['percent'] = ((int)(1000*$locales[$locale]['current']/$locales[$locale]['all']))/10;
    }
}



function projectTree($project_id) {
    $gpj = new GP_Project();
    return subProjectsOf($project_id);
}

function subProjectsOf($id) {
    global $gpdb;
    $prjs = $gpdb->get_results( "SELECT id FROM `$gpdb->projects` WHERE parent_project_id = ".$id );
    $subs = array();
    foreach($prjs as $prj) {
        $subs = array_merge($subs, subProjectsOf($prj->id));
    }
    if(empty($subs)) {
        return array($id);
    } else {
        return array_merge(array($id), $subs);
    }
}


echo json_encode($locales);
exit;

?>