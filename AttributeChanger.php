<?php

/*

	AREA OF PRACTICE IS AN ATTRIBUTE FIELD TO ADD

	//SET VALUES OF CHECKBOX ARE STORED COMMA SEPARATED

*/
	class AttributeChanger {

		global $tables, $DBstruct;

		$AttributeChangerPluggin;

		function turnOnAttributeChangerPluggin(){
			//NEED TO SQL QUERY THIS!!!!!!!!
			if(!isset($$GLOBALS['tables']['pluggins']['AttributeChanger'])){
				$GLOBALS['tables']['pluggins']['AttributeChanger'] = $AttributeChangerInfo = array('isOn' => true);
				$AttributeChangerPluggin = $GLOBALS['tables']['pluggins']['AttributeChanger'];

			}
			else{
				$GLOBALS['tables']['pluggins']['AttributeChanger']['isOn'] = true;
				$AttributeChangerPluggin = $$GLOBALS['tables']['pluggins']['AttributeChanger'];
			}
		}

		function testAttributeChangerOn() {
			if(!isset($GLOBALS['tables']['pluggins']['AttributeChanger'])){
				return false;
			}
			if(!isset($GLOBALS['tables']['pluggins']['AttributeChanger']['isOn']) {
				return false;
			}
			if($GLOBALS['tables']['pluggins']['AttributeChanger']['isOn'] == false) {
				return false;
			}

			else{
				if(!isset($AttributeChanger)){
					$AttributeChanger = $GLOBALS['tables']['pluggins']['AttributeChanger'];
				}
				return true;
			}
		}

		//the attribute changer is to be given a data set containing at least an email
			//it will query the existing list for email existence
			//else it is a new entry, can insert into a temp table or keep in program memory


		//[email] => array[attribute1,value]
		$New_Entry_List;

		//need to indicate which are modifying
		$Modify_Entry_List;

		//THIS HOLDS EMAILS WITH DUPLICATES
		$Duplicate_Attribute_Values_list;

		//THIS HOLDS EMAILS WITH SPECIFIED DUPLICATE ATTRIBUTES
		$Duplicate_Attributes;


		$attribute_list;

		function Initialize() {
			$query = sprintf('select * from %s', $GLOBALS['tables']['attribute']);
			$attribute_data_rows = Sql_Query($query);	

			if($attribute_data_rows) {
				$attribute_list = array();

				foreach ($attribute_data_rows as $key => $attribute_data) {
					if(!isset( ($attribute_data['id']) | ($attribute_data['name']) | ($attribute_data['type']) )) {
						//not known format, cannot use
					}
					else{
						$attribute_list[$attribute_data['name']] = array($attribute_data);
						if($attribute_data['type'] == ("radio"|"checkboxgroup"|"select"|"checkbox")) {
							if(!isset($attribute_data['tablename'])) {

							}
							else{
								$value_table_name = $table_prefix."listattr_".$attribute_data["tablename"];
								$value_query = sprintf("select * from %s", $value_table_name);
								$allowed_values  = Sql_Query($value_query);
								if($allowed_values) {
									$attribute_list[$attribute_data['name']]['allowed_values'] = $allowed_values;
								}
								else{
									$attribute_list[$attribute_data['name']]['allowed_values'] = '';
								}
							}
						}
						else{
							//is other input type
						}

						$Duplicate_Attributes[$attribute_data['name']]= array();
					}
				}
			}
			else{
				//no rows :S
			}
			$New_Entry_List = array();
			$Modify_Entry_List = array();
			$Duplicate_Attribute_Values_list = array();
			$Duplicate_Attributes = array();

		}
		
		function Updated_Test_Entry($entry) {
			//entry is [email]=>array (attribute, value)

			$changing_attributes = array();

			if(!array_key_exists("email", $entry)) {
				return false;
			}
			$email = $entry['email'];
			unset($entry['email']);

			if(!filter_var($email, FILTER_VALIDATE_EMAIL) ){
				return false;
			}

			$entry_query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email);
			$user_result = Sql_Query($entry_query);

			if(count($user_result) == 0) {

				if($entry_result){
					return true;
				}
				else{
					if(isset($New_Entry_List[$email])) {
						return true;
					}
					else{
						$New_Entry_List[$email] = array();
						return true;
					}
				}
			}

			foreach ($entry as $attribute => $new_attribute_value) {
				if(isset($attribute_list[$attribute])) {
					if(isset($user_result['id'])) {
						$attribute_query = sprintf("select * from %s where primary key = %s", $GLOBALS['tables']['user_attribute'], $attribute_list[$attribute]['id'].$user_result['id']);
						$current_user_attribute = Sql_Query($attribute_query);
					}


					//these are single choce values
					if($attribute_list[$attribute]['type'] == "radio"|"select") {

						//must check if the possible new value is an allowed value
						if(in_array($new_attribute_value, $attribute_list[$attribute]['allowed_values'])) {

							//this is if the returned user has an id, will always have an id if exists in the database
							if(isset($user_result['id'])) {

								//the return query for the user,attrubute does not match the new possible attribute value
								if($current_user_attribute != $new_attribute_value) {
									Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_list, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);

								}
								else{
									//already equals the currenttly set attribute value
								}
							}

							else{
								//no user info, add info to list
								Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $New_Entry_List, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
							}
						}
						else{
							//not an allowed value so skip
						}
					}

					else if($attribute_list[$attribute]['type'] == 'checkboxgroup'|'checkbox') {

						$exploded_attribute_values_array = explode(',', $value);

						$allowed_exploded_values = explode(',', $attribute_list[$attribute]['allowed_values']);
						$has_attributes = false;
						if(isset($user_result['id'])) {

							if($current_user_attribute != null) {
								$current_user_attribute_array = explode(',', $current_user_attribute);
								$has_attributes = true;
							}
						}

						foreach ($exploded_attribute_values_array as $key => $exploded_attribute_value) {

							if(in_array($exploded_attribute_value, $allowed_exploded_values)) {

								if($has_attributes == true) {
									//user definately exists, need to check if the current value is already one selected
									if(!in_array($exploded_attribute_value, $current_user_attribute_array)) {
										//if not in the array, add it to the change list
										Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_list); 
									}
								}
								else{
									if(!isset($user_result['id'])) {
										Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $New_Entry_List); 

									}
									//no current attributes, can definately add to list, user exists
									Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_list);								
								}
							}
						}
					}

					else if ($attribute_list[$attribute]['type'] == "date") {


					}

					else if ($attribute_list[$attribute]['type'] == "textarea"|"textline") {
						//this is if the returned user has an id, will always have an id if exists in the database
						if(isset($user_result['id'])) {

							//the return query for the user,attrubute does not match the new possible attribute value, so add to the list
							if($current_user_attribute != $new_attribute_value) {

								Add_Single_Entry_To_Change_List($email, $new_attribute_value, $changing_attributes, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);

							}
							else{
								//already equals the currenttly set attribute value
							}
						}
						else{
							// there is no user set, so all values are good for this type (make sure proper string format though)
							Add_Single_Entry_To_Change_List($email, $new_attribute_value, $changing_attributes, $Duplicate_Attribute_Values_list, $Duplicate_Attributes);
						}
					}
				}
			}
		}

		function Add_Single_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_list, $Duplicate_Email_List, $Duplicate_Attributes_List) {

			if(!isset($Modify_list[$email])) {
				$Modify_list[$email] = array();
			}
			if(!isset($Modify_list[$email][$attribute])){
				$Modify_list[$email][$attribute] = array($new_attribute_value);
				return;
			}
			if(in_array($new_attribute_value, $Modify_list[$email][$attribute])) {
				return;
			}
			else{
				array_push($Modify_list[$email][$attribute], $new_attribute_value);

				if(!isset($Duplicate_Email_List[$email])){
					$Duplicate_Email_List[$email] = true;
				}
				//indicate there are multiple entries for this email,attribute pair
				if(!isset($Duplicate_Attributes_List[$attribute][$email])) {
					$Duplicate_Attributes_List[$attribute][$email] = true;
				}
			}
		}

		function Add_Multi_Entry_To_Modify_Or_New_Entry_List($email, $new_attribute_value, $attribute, $Modify_list) {
			if(!isset($Modify_list[$email])) {
				$Modify_list[$email] = array();
			}
			if(!isset($Modify_list[$email][$attribute])){
				$Modify_list[$email][$attribute] = array($new_attribute_value);
				return;
			}
			if(in_array($new_attribute_value, $Modify_list[$email][$attribute])) {
				return;
			}
			else{

				array_push($Modify_list[$email][$attribute], $new_attribute_value);
			}
		}



		// function Add_Single_Entry_To_Change_List($email, $new_attribute_value, $attribute, $change_list, $Duplicate_Email_List, $Duplicate_Attributes_List) {
			
		// 	if(!isset($change_list[$attribute])) {

		// 		$change_list[$attribute] = array($new_attribute_value);

		// 	}
		// 	else{
		// 		//there is already a new value for this attribute.... push if this value is not existant and mark as new if not already set
		// 		if(in_array($new_attribute_value, $change_list[$attribute])) {
		// 			return;
		// 		}

		// 		array_push($change_list[$attribute], $new_attribute_value)

		// 		if(!isset($Duplicate_Email_List[$email])){
		// 			$Duplicate_Email_List[$email] = true;
		// 		}
		// 		//indicate there are multiple entries for this email,attribute pair
		// 		if(!isset($Duplicate_Attributes_List[$attribute][$email])) {
		// 			$Duplicate_Attributes_List[$attribute][$email] = true;
		// 		}
		// 	}
		// }

		// function Add_Multi_Entry_To_Change_List($new_attribute_value, $attribute, $change_list) {
		// 	if(!isset($change_list[$attribute])) {

		// 		$change_list[$attribute] = array($new_attribute_value);

		// 	}
		// 	else{
		// 		//there is already a new value for this attribute.... push if this value is not existant and mark as new if not already set
		// 		if(in_array($new_attribute_value, $change_list[$attribute])) {
		// 			return;
		// 		}

		// 		array_push($change_list[$attribute], $new_attribute_value)
		// 	}
		// }



		

	}

?>