<?php

class CourseCategories extends DefaultPluggin {
	global $tables, $DBstruct;


	function TurnOnCategories() {

		if(!isset($GLOBALS['CourseCategoryEntryStruct']){

			$GLOBALS['CourseCategoryEntryStruct'] = array(
				'email' =>array("varchar(255) not null","Email"),
				'user_id' => array("integer not null primary key auto_increment","sysexp:ID"),
				#'criteria_met' => array("string", "no"),

				"is_forced" => array("boolean not null", "sticky")
				"attribute" => array("array not null","attribute"),
				"date_entered" => array("datetime", "sysexp:Entered"),
				'date_modified' => array("datetime", "sysexp:Entered")
			);
		}

		if(!isset($GLOBALS['CourseCategoryStruct'])){
			$GLOBALS['CourseCategoryStruct'] = array(
				'category_id' =>array("integer not null primary key auto_increment","sysexp:ID"),
				'category_name' => array("varchar(255) not null","name"),

				#each category criteria can be [contains, does_not_contain, string_is, must_not_be, must_be, must_not_contain], attribute as string
										#or    attribute as int is [<, <=, >, >=] int
 )
				'category_criteria_1' => array("varchar(255)","criteria"),
				'category_criteria_2' => array("varchar(255)","criteria"),
				'category_criteria_3' => array("varchar(255)","criteria"),
				'category_criteria_4' => array("varchar(255)","criteria"),
				'category_criteria_5' => array("varchar(255)","criteria"),
				'category_description' => array("varchar(255)","description"),
				'date_entered' => array("datetime", "sysexp:Entered"),
				'date_modified' => array("datetime", "sysexp:Entered")
			);
		}

		if(!isset($GLOBALS['DBstruct']['courseCategories'])){
			$GLOBALS['DBstruct']['courseCategories'] = $GLOBALS['CourseCategoryStruct'];
		}



		if(isset($GLOBALS['courseCategoriesOn'])){
			if($GLOBALS['courseCategoriesOn'] == true){

				#this really shouldnt happen if above is true
				if(!isset($GLOBALS['tables']['courseCategories'])) {

					#need to find the real table prefix
					$GLOBALS['tables']['courseCategories'] = 'table_CourseCategories';
					if(!Sql_Check_For_Table($GLOBALS['tables']['courseCategories'])){
						createTable('courseCategories');
					}

				}
				return true;
			}

			else{
				#was false
				if(!isset($GLOBALS['tables']['courseCategories'])) {

					#need to find the real table prefix
					$GLOBALS['tables']['courseCategories'] = 'table_CourseCategories';
					#check if the table exists, create it otherwise
					if(!Sql_Check_For_Table($GLOBALS['tables']['courseCategories'])){
						createTable('courseCategories');
					}
					$GLOBALS['courseCategoriesOn'] = true;
					return true;
				}
				else{
					$GLOBALS['courseCategoriesOn'] = true;
					return true;
				}
			}

		}
		else{
			#the course categories have never been turned on
			if(!isset($GLOBALS['tables']['CourseCategories'])) {

				#need to find the real table prefix
				$GLOBALS['tables']['CourseCategories'] = 'table_courseCategories';

				if(!Sql_Check_For_Table($GLOBALS['tables']['courseCategories'])){
					createTable('courseCategories');
				}
				$GLOBALS['courseCategoriesOn'] = true;
				return true;
			}	
		}
	}

	function TurnOffCategories() {
		if(!isset($GLOBALS['courseCategoriesOn'])){
			#is not on and has never been on
			return false;
		}
		else{
			if($GLOBALS['courseCategoriesOn'] == false){
				#is already off
				return false;
			}
			else{
				$GLOBALS['courseCategoriesOn'] = false;
				return true;
			}
		}

	}

	function CheckCategoriesOn() {
		if(!isset($GLOBALS['courseCategoriesOn'])){
			return false;
		}
		else{
			return $GLOBALS['courseCategoriesOn'];
		}
	}



	function NewCategory($courseCategory) {

		if(!CheckCategoriesOn()){
			$GLOBALS['category_error_message'] = 'categories_off';
			return false;
		}
		else{
			#make sure category doesnt already exists
			if(isset($GLOBALS['tables'][$courseCategory])){
				$GLOBALS['category_error_message'] = 'category_already_created';
				return false;
			}
			else{
				$GLOBALS['tables'][$courseCategory] = 'table_'.$courseCategory;
				if(!isset($GLOBALS['DBstruct'][$courseCategory])) {
					#set the structure type
					$GLOBALS['DBstruct'][$courseCategory] = $GLOBALS['CourseCategoryEntryStruct'];
				}
				#make sure table doesnt already exist
				if(!Sql_Check_For_Table($GLOBALS['tables'][$courseCategory])){
					createTable($courseCategory);
				}
				return true;
			}
		}
	}



	function ModifyCategory($courseCategory, $modifyType, $newData) {

		if(!CheckCategoriesOn()){
			$GLOBALS['category_error_message'] = 'categories_off';
			return false;
		}
		#ensure proper function inputs
		else if($modifyType == null || !is_string($modifyType) || $newData == null || !is_string($courseCategory)) {
			$GLOBALS['category_error_message'] = 'incorrect_input';
			return false;
		}
		else{
			#make sure the course category table exits
			if(!isset($GLOBALS['tables'][$courseCategory])){
				$GLOBALS['category_error_message'] = 'category_does_not_exist';
				return false;
			}
			else{
				#make sure the modify type column exists in the table
				$column_test_query = sprintf('select * from %s where column_name = %s', $GLOBALS['tables'][$courseCategory], $modifyType);
				$req = Sql_Query($column_test_query);
				$row = Sql_Fetch_Row($req);
				if(!$row == $modifyType){
					$GLOBALS['category_error_message'] = 'column_in_modify_type_does_not_exist';
					return false;
				}

				#each column type has previously defined parameters, must include additional column types if new created
				else if(strcmp($modifyType, "category_name") == 0 ) {
					if(!is_string ($newData) ){
						$GLOBALS['category_error_message'] = 'new_data_is_not_string';
						return false;
					}
					
					else{
						$query_replace = sprintf('replace into %s (%s) values (%s)', $GLOBALS['tables'][$courseCategory], $modifyType, $newData);
						$result = Sql_Query($query_replace);
						return true;
					}
				}

				else if(strcmp($modifyType, "category_description") == 0 ) {
					#will just replace string with another string
					if(!is_string ($newData) ){
						$GLOBALS['category_error_message'] = 'new_data_is_not_string';
						return false;
					}
					
					else{
						$query_replace = sprintf('replace into %s (%s) values (%s)', $GLOBALS['tables'][$courseCategory], $modifyType, $newData);
						$result = Sql_Query($query_replace);
						return true;
					}
				}

				else if(strcmp($modifyType, "category_criteria") == 0 ) {
					#newData must be array( [add, modify, remove] => array(criteria=> array([ =, <=, >=, <, >  >], value) ))
					if(!is_array($newData)) {
						$GLOBALS['category_error_message'] = 'new_criteria_is_not_array';
						return false;
					}
					else{
						$return_results = array();
						$index =0;
						foreach ($newData as $modify_behaviour => $update_value) {
							if(!is_array($update_value)){
								$return_results[$index] = array(false => 'update_value_not_array_format');
							}
							else if(!sizeof($update_value)==1){
								$return_results[$index] = array(false => 'update_value_array_size_not_equal_to_1');
							}

							else {

								$good=true;
								foreach ($update_value as $critera => $rule) {
									if(!is_array($rule)){
										$return_results[$index] = array(false => 'update_rule_is_not_array');
										$good = false;
										break;
									}
									else if(sizeof($rule)!=1){
										$return_results[$index] = array(false => 'update_rule_array_size_not_equal_to_1');
										$good = false;
										break;
									}
								}
								if($good==false) {
									#already have the message in results
								}

								else switch($modify_behaviour) {
									case '=':
									#already ensured the following is good
										foreach ($update_value as $criteria => $rule) {
											if(!is_string($criteria)){
												$return_results[$index] = array(false => 'update_criteria_is_not_string');
												break;
											}
											else{

											}
										}
										
									case '<=':

									case '>=':

									case '<': 

									case '>':

									case 'must_contain':

									case 'must_not_contain':

									case 'must_be':

									case 'must_not_be':

									case 'string_is':

									case 'contains':

									case 'does_not_contain':

									default:
										$return_results[$index] = array(false => 'modify_behaviour_incorrect_format');
								}
							}
							$index++;
						}
						#then must cleanup category
					}

				}
			}
		}

	}

	function DeleteCategory($courseCategory) {

	}


	function AddEmailToCategory($courseCategory, $email) {

	}

	function RemoveEmailFromCategory($courseCategory, $email) {

	}

	function CheckEmailForCategoryMemberShip($email) {

	}

	function RemoveEmailFromAllCategories($email) {

	}


	function ListAllCategories() {

	}

	function ListCategoryMembers($courseCategory) {

	}

	function CleanUpCategory($courseCategory) {

	}

	function ParseExternalDatabase($databaseType, $data) {

	}

}

?>