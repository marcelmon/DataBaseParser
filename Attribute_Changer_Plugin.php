<?php
require_once dirname(__FILE__).'/defaultplugin.php';

class Attribute_Changer_PLugin extends phplistPlugin {

	

	function adminMenu() {
    	return $this->pageTitles;
	}

    function __construct()
    {
        parent::__construct();
        this->pageTitles = array( // Entries in the plugin menu of the dashboard
			'pluginpage' => 'Description of this page in my plugin',
		);
		  
		this->topMenuLinks = array( // Entries in the top menu at the top of each page
			'pluginpage' => array('category' => 'subscribers'),
		);

		this->$coderoot = PLUGIN_ROOTDIR.'Attribute_Changer_PLugin/';
    }


}




?>