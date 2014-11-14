<?php
/*
 * Main template for RAD framework. 
 * @author Steve Ryan, stever@syntithenai.com 2014
 */
function radindex_ALL(Web $w,$p) {
	if ($p['classname'] && count(trim($p['classname']))>0) {
		if (class_exists($p['classname'])) {
			$o=new $p['classname']($w);
			//print_r(array ($classname, $id,$o->canEdit($this->w->Auth->user())));
			$meta=[];
			$o->id=1;
			$meta['className']=$p['classname'];
			$meta['canCreate']=$o->canCreate($w->Auth->user());
			$meta['canEdit']=$o->canEdit($w->Auth->user());
			$meta['canView']=$o->canView($w->Auth->user());
			$meta['canDelete']=$o->canDelete($w->Auth->user());
			$meta['canList']=$o->canList($w->Auth->user());
			$meta['databaseColumns']=implode(',',$o->getDbTableColumnNames());
			$labels=[];
			$cols=$o->getDbTableColumnNames();
			foreach($cols as $cnk => $column) {
				$labels[]=$o->getHumanReadableAttributeName($column);
			}
			$meta['propertyUITypes']=implode(",",$o->getPropertyUITypes($cols));
			$meta['labels']=implode(',',$labels);
			$meta['titleTemplate']=$o->getTitleTemplate();
			$meta['searchFields']=implode(",",$o->getSearchFields());
			$meta['editFields']=implode(",",$o->getEditFields());
			$meta['viewFields']=implode(",",$o->getViewFields());
			$meta['listFields']=implode(",",$o->getListFields());
			
			$w->ctx('meta',$meta);
		} else {
			echo "Invalid  class";
			die();
		}
	} else {
		echo "No class provided";
		die();
	}
}

?>
