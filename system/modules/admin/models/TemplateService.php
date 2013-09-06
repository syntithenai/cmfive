<?php
class TemplateService extends DbService {
	
	private $div_lib = "div-4.4/div.php";
	
	/**
	 * 
	 * @param in $id
	 * @return Template
	 */
	function getTemplate($id) {
		return $this->getObject("Template", $id);
	}
	
	/**
	 * Get a list of Template objects for module and category.
	 * 
	 * @param string $module default null
	 * @param string $category default null
	 * @param string $includeInactive default false
	 * @param string $includeDeleted default false
	 * @return array(<<Template>>)
	 */
	function findTemplates($module = null, $category = null, $includeInactive = false, $includeDeleted = false) {
		if ($module) {
			$where['module']=$module;
		}
		if ($category) {
			$where['category']=$category;
		}
		if (!$includeInactive) {
			$where['is_active']=1;
		}
		if (!$includeDeleted) {
			$where['is_deleted']=0;
		}
		return $this->getObjects("Template",$where);
	}
	
	/**
	 * Merging a template with data.
	 * 
	 * For $template you can pass the following:
	 * 
	 * 1) the ID of a Template object
	 * 2) a Template object
	 * 3) a path to a template file
	 * 4) template code as a string
	 * 
	 * @param int|Template|string $template
	 * @param array|object $data
	 * @return string
	 */
	function render($template, $data) {
		if (empty($template)) return;
		
		// falling through the options:
		
		// if passing a template's id
		if (is_int($template)) {
			$template = $this->getTemplate($template);
		}
		
		// if passing a Template object
		if (is_a($template, "Template")) {
			$template = $template->body;
		}
		
		// if passing a file path or string template
		if (is_string($template)) {
			include_once $this->div_lib;
			return new div($template,$data);
		}
		
	} 
}