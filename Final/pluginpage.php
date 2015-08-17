<?php



if (!defined('PHPLISTINIT')) die(); ## avoid pages being loaded directly
if ($GLOBALS["commandline"]) {
 echo 'not to oppened by command line';
 die();
}
require_once(dirname(__FILE__).'Attribute_Changer_Plugin.php');
$javascript_src = dirname(__FILE__) . '/Attribute_Changer_PLugin/Script_For_Attribute_Changer.js';



//CHANGE THE PAGE PRINT TO REFLECT THE PROPER PLUGIN DIR
$page_print =  '
<div>Attribute Changer</div>
<div id="error_printing"></div>
<form action="upload_file.php" method="post" enctype="multipart/form-data" id="file_upload_form">
    Select file to upload:
    (must be comma separated text)
    <input type="file" name="attribute_changer_file_to_upload" id="attribute_changer_file_to_upload">
    <input type="button" value="attribute_changer_upload_file_button" name="attribute_changer_upload_file_button" id="attribute_changer_upload_file_button" onClick="Test_Upload_File()">
</form>
<form action="" method="post" enctype="multipart/form-data" id="text_upload_form">
    Copy file to upload:
    (must be comma separated text)
    <input type="text" name="attribute_changer_text_to_upload" id="attribute_changer_text_to_upload">
    <input type="button" value="attribute_changer_upload_text" name="attribute_changer_upload_text" onClick="Test_Upload_Text()">
    desired_file_name:<input type="text" name="attribute_changer_text_name">
</form>'

;
if(!isset($_POST)) {
	
    print('<html><head><script src="'.$javascript_src.'"></script></head><body>'.$page_print.'</body></html>');
}

else{
    printf('<html><head><script src="'.$javascript_src.'"></script></head><body>SOMETHING HAPPENED, HERES THE FRONT :\n'.$page_print.'</body></html>');
}

?>


