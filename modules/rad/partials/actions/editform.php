<?php
/*
 * Edit form template for RAD framework. 
 * @author Steve Ryan, stever@syntithenai.com 2014
 */
function editform_ALL(Web $w,$p) {
	$lines=[];
	if ($p['classname'] && count(trim($p['classname']))>0 && class_exists($p['classname'])) {
		$o=new $p['classname']($w);
		$lines[] = array("id","hidden","id",null,null,null,array("data-editfield"=>"true","data-field"=>"id"));
		foreach($o->getEditFields() as $ek => $editField) {
			$meta=$o->getPropertyMeta($editField);
			$type=$meta['type'];
			$label=$meta['label'];
			$options=[];
			if (array_key_exists('options',$meta))  $options=$meta['options'];
			
			$lines[] = array($label,$type,$editField,null,$options,null,null,array("data-editfield"=>"true","data-field"=>$editField));
		}
		$buttons=[];
		$buttons[] = array('','button','','Save',null,'',null,array("data-action"=>"save"));
		$buttons[] = array('','button','','Cancel',null,'',null,array("data-action"=>"close"));
	}
	$w->ctx('lines',$lines);
	$w->ctx('buttons',$buttons);
}

?>
