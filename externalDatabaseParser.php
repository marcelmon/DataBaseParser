<?php

/*


	definitions:

		$DataBaseParsePluggin = $GLOBALS['tables']['pluggins']['DataBaseParserPluggin'] contains all pluggin info

		$DataBaseParsePluggin = [is_on : true/false to test if pluggin is on , 
								errors : for relaying error messages back if pluggin is on, 
								current incremental id
								profiles : the website sraper profiles, contains: incremental_id, admin_ids, root_dir, profile name, url, all_previous_site_data_and_rules, date_modified, current_increment]
								]


*/
class DatabaseParser extends DefaultPlugin{

		global $tables, $DBstruct;

		$DataBaseParsePluggin;

		function turnOnDatabaseParser(){
			//NEED TO SQL QUERY THIS!!!!!!!!
			if(!isset($$GLOBALS['tables']['pluggins']['DataBaseParserPluggin'])){
				$GLOBALS['tables']['pluggins']['DataBaseParserPluggin'] = $databaseParserInfo = array('isOn' => true);
				$DataBaseParsePluggin = $GLOBALS['tables']['pluggins']['DataBaseParserPluggin'];
				$DataBaseParserPluggin['current_incremental_id'] = 1;
				$DataBaseParserPluggin['is_running_webiste_download'] = false;

			}
			else{
				$GLOBALS['tables']['pluggins']['DataBaseParserPluggin']['isOn'] = true;
				if($DataBaseParserPluggin['is_running_webiste_download'] === true) {
					//THIS SHOULDNT HAPPEN WHEN IT WAS OFF
				}
				$DataBaseParsePluggin = $$GLOBALS['tables']['pluggins']['DataBaseParserPluggin'];
			}
		}

		function testDatabaseParserOn() {
			if(!isset($GLOBALS['tables']['pluggins']['DataBaseParserPluggin'])){
				return false;
			}
			if(!isset($GLOBALS['tables']['pluggins']['DataBaseParserPluggin']['isOn']) {
				return false;
			}
			if($GLOBALS['tables']['pluggins']['DataBaseParserPluggin']['isOn']==false) {
				return false;
			}

			else{
				if(!isset($DataBaseParserPluggin)){
					$DataBaseParserPluggin = $GLOBALS['tables']['pluggins']['DataBaseParserPluggin'];
				}
				return true;
			}
		}


		function CreateProfile ($profile_name, $id, $url, $root_dir) {
			if(testDatabaseParserOn() == false) {
				return null;
			}

			if(!isset($profile_name, $id, $url, $root_dir)) {
				$DataBaseParsePluggin['errors'] = 'BAD INPUT';
				return null;
			}
			else{
				if(isset($DataBaseParsePluggin['profiles'][$profile_name])) {
					$DataBaseParsePluggin['errors'] = 'PROFILE NAME ALREADY SET';
					return null;
				}
				if(@get_headers($url)[0] == 'HTTP/1.1 404 Not Found') {
					$DataBaseParsePluggin['errors'] = 'BAD URL';
					return null;
				}
				//make sure is from root
				if(substr($root_dir, 0, 1) != '/') {
					$DataBaseParsePluggin['errors'] = 'ROOT DIRECTORY MUST BE HARD LINK';
					return null;
				}
				//make sure is in some phplistpluggin/profilename_and_id for overwrite safe
				if(strpos($root_dir, '/phplistpluggin/'.$profile_name.$DataBaseParsePluggin['profiles'][$profile_name]['incremental_id']) == false) {
						$DataBaseParsePluggin['errors'] = 'ROOT DIRECTORY IS NOT ALLOWED';
						return null;
				}
				
				if(file_exists($root_dir)) {
					$DataBaseParsePluggin['errors'] = 'ROOT DIRECTORY ALREADY EXISTS';
					return null;
				}
				else{
					mkdir($root_dir);
					$DataBaseParsePluggin['profiles'][$profile_name] = new array();
					$DataBaseParsePluggin['profiles'][$profile_name]['root_dir'] = $root_dir;
					$DataBaseParsePluggin['profiles'][$profile_name]['profile_name'] = $profile_name;
					$DataBaseParsePluggin['profiles'][$profile_name]['incremental_id'] = $DataBaseParserPluggin['incremental_id'];
					$DataBaseParsePluggin['profiles'][$profile_name]['admin_permission'] = $id;
					$DataBaseParsePluggin['profiles'][$profile_name]['url'] = $url;
					$DataBaseParsePluggin['profiles'][$profile_name]['current_increment'] = 0;
					$DataBaseParserPluggin['incremental_id']++;
					$newProfile = $DataBaseParsePluggin['profiles'][$profile_name];

					$query = sprintf('insert into %s (profile_name, incremental_id, root_dir, admin_permission, url, current_increment) values %s', $profile_name, $newProfile['incremental_id'], $root_dir, $id, $url, $newProfile['current_increment']);
					$result = Sql_Query($query);
					if(!$result) {
						$DataBaseParserPluggin['errors'] = 'SQL ERROR';
						unset($newProfile);
						rmdir($root_dir);
						return null;
					}
					return $newProfile;
				}
			}
		}





		function DownloadSite($profile_name, $id, $delete_previous_info) {

			//get the profile data from the sql database, if not set must set
			//check for correctedness, download
			if(!isset($DataBaseParsePluggin['profiles'][$profile_name])) {
				$DataBaseParsePluggin['errors'] = 'PROFILE NOT SET';
				return null;
			}

			$scraper_profile = isset($DataBaseParsePluggin['profiles'][$profile_name];
			if(in_array($id, $scraper_profile['admin_permission']) == false ){
					$DataBaseParsePluggin['errors'] = 'DO NOT HAVE ACCES PERMISSION TO PROFILE';
					return null;
			}

			else{
				if(@get_headers($scraper_profile['url'])[0] == 'HTTP/1.1 404 Not Found') {
					$DataBaseParsePluggin['errors'] = 'BAD URL';
					return null;
				}
				if(!is_dir($scraper_profile['root_dir'])) {
					$DataBaseParsePluggin['errors'] = 'NO ROOT PROFILE DIRECTORY';
					return null;
				}
				else{
					if(!is_dir($scraper_profile['root_dir'].'/website_data')) {
							mkdir($scraper_profile['root_dir'].'/website_data');
					}

					$new_data_directory = $scraper_profile['root_dir'].'/website_data/'.($scraper_profile['current_increment']+1);
					if(is_dir($new_data_directory)) {
						$DataBaseParsePluggin['errors'] = 'UNEXPECTED DIRECTORY PRESENT @: '.$new_data_directory;
						return null;
					}
					else{
						
						mkdir($scraper_profile['root_dir'].'/website_data/'.($scraper_profile['current_increment']+1);
						$scraper_profile['current_increment']++;

						$output = array();
						$return = array();
						$pid = pcntl_fork();

						if ($pid == -1) {
							//if this is the case, 
     						die('could not fork');
     					}

						} 
						else if ($pid) {
						     // we are the parent
						     pcntl_wait($status); //Protect against Zombie children

						} 
						else {
							while($DataBaseParserPluggin['is_running_webiste_download'] == true){
								//WAIT FOR THE CURRENT DOWNLOADING WEBSITE
								//do a pcntrl wait for pid
							}
							$DataBaseParserPluggin['is_running_webiste_download'] = true;
							$DataBaseParserPluggin['downloading_profile'] = $profile_name;
							$DataBaseParserPluggin['current_running_download_pid'] = $pid;
							exec('httrack '.$scraper_profile['url'].' -O '.$scraper_profile['root_dir'].'&', $output, $return);
							//or some other thing
							while($output != 'Done'){

							}
							$DataBaseParserPluggin['is_running_webiste_download'] = false;
							exit();
						}
						

					}
				}
			}

			//return rules
		}

		function DisplayRuleSelector($profile_name, $id) {
			if(!isset($DataBaseParsePluggin['profiles'][$profile_name])) {
				$DataBaseParsePluggin['errors'] = 'PROFILE NOT SET';
				return null;
			}

			$scraper_profile = isset($DataBaseParsePluggin['profiles'][$profile_name];

			if(in_array($id, $scraper_profile['admin_permission']) == false ){
				$DataBaseParsePluggin['errors'] = 'DO NOT HAVE ACCES PERMISSION TO PROFILE';
				return null;
			}

			if($DataBaseParserPluggin['downloading_profile'] == $profile_name) {
				$DataBaseParsePluggin['errors'] = 'CURRENTLY DOWNLOADING DATA';
				return null;
			}

			else {
				(!is_dir($scraper_profile['root_dir'].'/website_data')) {
					$DataBaseParsePluggin['errors'] = 'NO WEBSITE DATA DIRECTORY SET FOR PROFILE';
					return null;
				}

				if(!is_dir($scraper_profile['root_dir'].'/website_data/'.$scraper_profile['current_increment'])) {
					$DataBaseParsePluggin['errors'] = 'EXPECTED CURRENT WEBSITE DATA DIRECTORY DOES NOT EXIST';
					return null;
				}

				
			}



		}




		///////////////////////////

		//when the xml parser goes through and finds a new entry, it must gather the data then call this
		function AddChangesEntry($new_entry) {


			$attribute_values_to_check;

			//[att_id, new_value]
			$new_values;

			$value_is_good;

			$has_email=false;
			$is_new_email = false;
			$user_id;


			//new entry of form [ <email, ...> <atribute_id, value> ]
			if($new_entry==null || !is_array($new_entry) || !isset($new_entry['email'])) {
				$DataBaseParsePluggin['errors'] = 'BAD INPUT';
				return false;
			}

			foreach ($new_entry as $attribute_id => $value) {

				//may not be the first value, so use true/false to ensure good
				if($attribute_id=='email') {
					//check email format
					if(!filter_var($value, FILTER_VALIDATE_EMAIL)){
						$DataBaseParsePluggin['errors'] = 'NOT REAL EMAIL';
						return false;
					}

					$has_email = true;
					$email = $value;

					//check if the email already exists as a user
					$query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $value);
					$result = Sql_Query($query);
					if(!$result){
						$is_new_email=true;

					}
					else{
						//get the user id of preexisting email
						$is_new_email = false;
						$req = Sql_Fetch_Row($result);
						$user_id = $req['id'];
					}
				}
				//each attribute is passed to the function as attribute id,value
				//check if the attribute is infact good
				else{
					$query = sprintf('select * from %s where id = "%s"', $GLOBALS['tables']['attribute'], $atribute_id);
					$result = Sql_Query($query);
					if($result){
						switch ($result['type']) {
							//THIS NEEDS CLEANUP////////////////////

							//make sure the value is good and proper format
							//else set the values properly or set $value_is_good to false
							//some types are unchangeable
							//dates must be formatted correctly, date called to bar should never be changed, some value are int only, etc
							//follow the preset rules
							case 'textline' :
							case 'checkbox' :
							case 'checkboxgroup' :
 	 						case 'select' :
 	 						case 'hidden' :
 	 						case 'textarea' :
  							case 'radio':
  							case 'Date' :

  							default:
						}
						//good values added to array to search with
						if($value_is_good == true) {
							$attribute_values_to_check[$atribute_id] = $value;
						}
					}
					else{
						//the attribute doesnt exist
					}
				}
			}

			if($has_email==false) {
				$DataBaseParsePluggin['errors'] = 'NO EMAIL PRESENTED';
				return false;
			}

			else{
				//a pre existing email will have attributes already set, if it is unchanged, do not show, else add to the list of changes presented
				if($is_new_email == false) {
					foreach ($attribute_values_to_check as $atribute_id => $value_to_check) {
						$query = sprintf('select * from %s where primary key = "%s"', $GLOBALS['tables']['user_attribute'], $atribute_id.','.$user_id);
						$result = Sql_Query($query);

						$req = Sql_Fetch_Row($result);
						if($req['value']!=$value_to_check) {
							$new_values[$atribute_id.','.$user_id] = $value_to_check;
						}
					}
					$DataBaseParsePluggin['message_for_return'] = 'CURRENT USER ID = '.$user_id;
					return $new_values;		
				}
				//non prexisting emails are all to be changed
				else{
					$DataBaseParsePluggin['message_for_return'] = 'NEW EMAIL = '.$email;
					return $attribute_values_to_check;
				}
			}
		}


		function ScrapeSingleHTMLFile($is_multi_entry, $file_path, $attribute_rules) {
			if(!isset($attribute_rules['html']['email'])) {
				$DataBaseParsePluggin['errors'] = 'NO EMAIL RULE';
				return false;
			}
			if(!isset($attribute_rules['html']['email']['get_from_element']) && !isset($attribute_rules['html']['email']['get_from_other'])) {
				$DataBaseParsePluggin['errors'] = 'INCORRECT EMAIL RULE';
				return false;
			}

			$new_entry_table;

			$new_entry;

			if($is_multi_entry == true) {
				
			}

			else if($is_multi_entry==false) {
				$dom = new DOMDocument();
				$dom >loadHTMLfile($file_name);
						
				//check the html file for the specified element that contains the email
				//if not present then the file is not scrapped
				if(isset($attribute_rules['html']['email']['get_from_element'])) {
					if($dom->getElementById($attribute_rules['html']['email']['get_from_element'][0])){
						$new_email = $dom->getElementById($attribute_rules['html']['email']['get_from_element'][0]).innerHTML();
						//ensure the email is proper format
						if(!filter_var($new_email, FILTER_VALIDATE_EMAIL)){
							$file_is_good= false;
							return null;
						}
						else{
							$new_entry['email'] = $new_email
						}
					}
					else{
						$file_is_good= false;
						return null;
					}

				}

				if($file_is_good == true && isset($attribute_rules['element_id_to_attribute_id'])) {
					foreach ($attribute_rules['element_id_to_attribute_id'] as $element_id => $attribute) {
						if($dom->getElementById($element_id )){
							$new_entry[$attribute] = $dom->getElementById($element_id )->innerHTML();

						}
						else{
							//cant set the element
						}
					}
				}
				return $new_entry;
			}
		}

		function TestFileGood($file_name, $file_rules) {

			$file_is_good == true
			$filetype = explode('.',$file_name);

			if(sizeof($filetype)!=2) {
				$DataBaseParsePluggin['errors'] = 'FILE WRONG FORMAT';
				return null;
			}

			if(isset($file_rules['exclude_file_types'])) {

				foreach ($file_rules['exclude_file_types'] as $key => $value) {
					if($value == $filetype[1]) {
						$DataBaseParsePluggin['errors'] = 'FILE TYPE EXCLUDED';
						return null;
					}
				}
			}
			//check file names
			if( $file_is_good == true && isset($file_rules['exclude_files_containing'])){
				$basename = explode('.',$file_name);

				foreach ($file_rules['exclude_files_containing'] as $key => $value) {
					if(preg_match($value, $basename[0])){
						$DataBaseParsePluggin['errors'] = 'FILE TYPE EXCLUDED';
						return null;
					}
				}			
			}
			return $filetype[1];
		}


		function BuildChangesTable($new_entry_type, $root_directory, $attribute_rules, $file_rules) {


			//set different rules for different file types


			//assume a function to parse an entry exists

			//this funciton must follow the rules to parse

			//the email rule is the only attribute rule required
			if(!isset($attribute_rules['email'])) {
				$DataBaseParsePluggin['errors'] = 'NO EMAIL RULE';
				return false;
			}
			// if(!isset($attribute_rules['email']['get_from_element']) && !isset($attribute_rules['email']['get_from_other'])) {
			// 	$DataBaseParsePluggin['errors'] = 'INCORRECT EMAIL RULE';
			// 	return false;
			// }

			//[email, array(attribute, value)]
			$entry_table;
			$single_entry;
			$file_type;

			$entry_table_duplicates;


			if($new_entry_type == 'one_per_file'){

				//scan for all files within the set root directory
				$files = scandir($root_directory);
				foreach ($files as $file_index => $file_name) {
					//bad files include ones designated as bad via name and type restrictions and if contains no email
					$file_type = TestFileGood($file_name, $file_rules);

					if($file_type == null) {

					}
					
					else{
						switch($file_type) {
							case 'html':
								if(!isset($attribute_rules['html'])){
									//skip the file then
								}
								else{
									if(!isset($attribute_rules['html']['email'])){
										//skip the file
									}
									else{
										$single_entry = ScrapeSingleHTMLFile(false, $root_directory.'/'.$file_name, $attribute_rules['html']);
										if($single_entry == null) {
											
										}
										else{
											if(!isset($single_entry['email'])) {

											}
											else{
												if(isset($entry_table[$single_entry['email']])) {
													//the email was already found, must add as secondary
													if(!isset($entry_table_duplicates[$single_entry['email']])) {
														//here there is no duplicate, add to duplicate found list and the entry in the entry table is an array of all

														$temp_data = $entry_table[$single_entry['email']];
														$entry_table[$single_entry['email']] = new array();
														array_push($entry_table[$single_entry['email']], $entry_table[$single_entry);
														$entry_table_duplicates[$single_entry['email']] = 1;
													}
													else{
														//there was already at least one duplicate, just push the new values onto the array
														array_push($entry_table[$single_entry['email']], $entry_table[$single_entry);
														$entry_table_duplicates[$single_entry['email']]++;
													}
													
												}
												else{
													//there was no email yet found, just add to the entry table
													$entry_table[$single_entry['email']] = $single_entry;
												}
											}
										}
									}
								}

							case 'spreadsheet':

							default:
						}
					}
				}
				//by this point all files in the root directory have been scraped
				return $entry_table;
			}
			else if($new_entry_type == 'multiple_per_file') {
				//either each row in a table or each element similar too, also specify just one file or many
			}

		}

		function DisplayChangesTable() {




		}




}



?>