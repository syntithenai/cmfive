<?php
/*
 * Edit form template for RAD framework. 
 * @author Steve Ryan, stever@syntithenai.com 2014
 */
function radeditform_ALL(Web $w,$p) {
	//print_r($p);
	$lines=[];
	if ($p['classname'] && count(trim($p['classname']))>0 && class_exists($p['classname'])) {
		$o=new $p['classname']($w);
		$lines[] = array("Edit","section");
		$lines[] = array("id","hidden","id",null,null,null,null,array("data-role"=>"hiddenfield","data-field"=>"id"));
		foreach($o->getEditFields() as $ek => $editField) {
			$meta=$o->getPropertyMeta($editField);
			$type=$meta['type'];
			$label=$meta['label'];
			$options=[];
			if (array_key_exists('options',$meta))  $options=$meta['options'];
			
			$lines[] = array($label,$type,$editField,null,$options,null,null,array("data-role"=>"editfield","data-field"=>$editField));
		}
		$buttons=[];
		$buttons[] = array('text'=>'Save','type'=>'button','class'=>'','extraAttributes'=>array("data-action"=>"save"));
		$buttons[] = array('text'=>'Cancel','type'=>'button','class'=>'','extraAttributes'=>array("data-action"=>"close"));
	}
	$w->ctx('lines',$lines);
	$w->ctx('buttons',$buttons);
}

?>
