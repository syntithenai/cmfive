<?php
/**
 * Song data model
 * 
 * @author Steve Ryan
 */
class RadDbObject extends DbObject {

	public function getAllFields() {
		return array('title','data');
	}
	public function getSearchFields() {
		return $this->getAllFields();
	}
	// TODO FOR ADVANCED SEARCH
	public function getSearchConfig() {
		return array('title'=>[],'data'=>[]);
	}
	public function getEditFields() {
		return $this->getAllFields();
	}
	public function getViewFields() {
		return $this->getAllFields();
	}
	public function getListFields() {
		return $this->getAllFields();
	}
	public function getPropertyUIType($property) {
		return 'text';
	}
	public function getPropertyUITypes($columns) {
		$types=[];
		foreach ($columns as $k=>$column) {
			$types[]=$this->getPropertyUIType($column);
		}
		return $types;
	}
	public function getPropertyMeta($property) {
		$meta=[];
		$meta['type']=$this->getPropertyUIType($property);
		$meta['label']=$this->getHumanReadableAttributeName($property);		
		return $meta;
	}
	
	/***************************************
	 * Accessors for title field
	 * Used by restexample module to render the titlefield into the template for JS
	 * *************************************/
	function getTitleTemplate() {
		return $this->__titleTemplate ? $this->__titleTemplate : '' ;
	}
	
	function setTitleTemplate($v) {
		return $this->__titleTemplate =$v;
	}
	
	private function _titleFromTemplate() {
		$titleTemplate=$this->getTitleTemplate();
			while (strpos($titleTemplate,'$')!==FALSE) {
				$start=strpos($titleTemplate,'$');
				$end=strpos($titleTemplate,'$',$start);
				$marker=substr($titleTemplate,$start,($end - $start)); 
				$titleTemplate=str_replace($marker,$this->__get(substr($marker,1)));
				print_r($start,$end,$marker,$titleTemplate);
			}
			$title=$this->$titleTemplate;
			return $title;
	}
    /**
     *
     * intermediate method to facilitate transition from
     * selectTitle to getSelectOptionTitle
     */
    function _selectOptionTitle() {
        $title = $this->getSelectOptionValue();
		// STEVER HACK IN OPTION TO SET TITLE FIELD ON OBJECT
        if ($this->getTitleTemplate() && property_exists(get_class($this),$this->getTitleTemplate())) {
			$title=$this->_titleFromTemplate();
        } else if (property_exists(get_class($this), "title")) {
            $title = $this->title;
        } else if (property_exists(get_class($this), "name")) {
            $title = $this->name;
        }
        return $title;
    }

    /**
     * is used by the Html::select() function to display this object in
     * a select list. Could also be used by other similar functions.
     */
    function getSelectOptionTitle() {
        return $this->_selectOptionTitle(); // only until all references are resolved
    }

}	

?>
