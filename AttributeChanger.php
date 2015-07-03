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


		$attribut_list;

		function Initialize() {
			$query = sprintf('select * from %s', $GLOBALS['tables']['attribute']);
			$attribute_data_rows = Sql_Query($query);	

			if($attribute_data_rows) {

				foreach ($attribute_data_rows as $key => $attribute_data) {
					if(!isset( ($attribute_data['id']) | ($attribute_data['name']) | ($attribute_data['type']) )) {
						//not known format, cannot use
					}
					else{
						$attribut_list[$attribute_data['name']] = array($attribute_data);
						if($attribute_data['type'] == ("radio"|"checkboxgroup"|"select"|"checkbox")) {
							if(!isset($attribute_data['tablename'])) {

							}
							else{
								$value_table_name = $table_prefix."listattr_".$attribute_data["tablename"];
								$value_query = sprintf("select * from %s", $value_table_name);
								$allowed_values  = Sql_Query($value_query);
								if($allowed_values) {
									$attribut_list[$attribute_data['name']]['allowed_values'] = $allowed_values;
								}
								else{
									$attribut_list[$attribute_data['name']]['allowed_values'] = '';
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

			$good_attributes = array();

			foreach ($entry as $attribute => $new_attribute_value) {
				if(isset($attribute_list[$attribute])){

					if($attribute_list[$attribute]['type'] == "radio"|"select") {

						if(in_array($new_attribute_value, $attribute_list[$attribute]['allowed_values'])) {
								if($user_result) {

									$attribute_query = sprintf("select * from %s where primary key = %s", $GLOBALS['tables']['user_attribute'], $attribute_list[$attribute]['id'].$user_result['id']);
									$current_user_attribute = Sql_Query($attribute_query);

									if($current_user_attribute != $new_attribute_value) {
										array_push($changing_attributes[$attribute], $new_attribute_value);
									}

								}

							}
							else{
								//not a good attribute
							}
						}
					}
					if($attribute_list[$attribute]['type'] == 'checkboxgroup'|'checkbox') {

						$exploded_attribute_values = explode(',', $new_attribute_value);

						foreach ($exploded_attribute_values as $key => $exploded_attribute) {
							
						}
					}
				}
			}

		}

		function Test_Entry($entry) {
			//entry is [email]=>array (attribute, value)
			if(!array_key_exists("email",$entry)) {
				return false;
			}
			$email = $entry['email'];
			unset($entry['email']);

			if(!filter_var($email, FILTER_VALIDATE_EMAIL) ){
				return false;
			}


			if(count($entry) == 0) {
				$query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email);
				$result = Sql_Query($query);
				if($result){
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

			else{

				$good_attributes = array();
				

				foreach ($entry as $attribute => $value) {
					
					if(isset($attribute_list[$attribute])) {

						switch($attribute_list[$attribute]['type']) {

							case "radio"|"select":

								if(in_array($value, $attribute_list[$attribute]['allowed_values'])) {
									$good_attributes[$attribute] = $value;
								}
								else{
									//not a good attribute
								}

							case 'date':

							case 'checkboxgroup'|'checkbox':

								$exploded_values = explode(',', $value);
								$allowed_exploded_values = explode(',', $attribute_list[$attribute]['allowed_values']);
								foreach ($exploded_values as $key => $exploded_attribute_value) {
									if(in_array($exploded_attribute_value, $allowed_exploded_values)) {
										if(!isset($good_attributes[$attribute])) {
											//MIGHT BE (,) w/  '('  or  ')' 
											$good_attributes[$attribute] = $exploded_attribute_value;
										}
										else{
											$good_attributes[$attribute] = $good_attributes[$attribute].','.$exploded_attribute_value;
										}
									}
								}

							default:
								$good_attributes[$attribute] = $value;
						}
						
					}
					else{

					}
				}

				$query = sprintf('select * from %s where email = %s', $GLOBALS['tables']['user'], $email);
				$user_result = Sql_Query($query);

				$changing_attributes = array();

				if($user_result){
					//there is a user with this email
					
					if(!isset($user_result['id'])){
						///.SHOULDNT
					}

					//iterate through all the potential attribute changes to build a list of possible changes to display
					foreach ($good_attributes as $attribute => $value) {
						$attribute_query = sprintf("select * from %s where primary key = %s", $GLOBALS['tables']['user_attribute'], $attribut_list['id'].$user_result['id']);
						
						$current_user_attribute = Sql_Query($attribute_query);

						if(isset($current_user_attribute)) {

							if($attribute_list[$attribute]['type'] == 'checkbox') {
								
								// NEED TO SET WAY TO REMOVE VALUES --> using sticky values
								$exploded_values = explode(',', $value);
								//////
								$changed_values = array();

								$current_change_values = explode(',' , $current_user_attribute['value']);
								foreach ($exploded_values as $key => $individual_value) {
									if(!in_array($individual_value, $current_change_values)) {
										array_push($changed_values, $individual_value);
									}
								}
								if(count($changed_values)==0){

								}
								else{
									$changing_attributes[$attribute] = $changed_values;
								}
							}


							else if($current_user_attribute['value'] != $value) {
								$changing_attributes[$attribute] = $value;
							}
						}

					}

					if(isset($Modify_Entry_List[$email])) {

						foreach ($changing_attributes as $attribute => $value) {



							if(isset($Modify_Entry_List[$attribute])){
								//there is already at least 1 new attribute for this email
								$is_already_included = false;
								foreach ($Modify_Entry_List[$attribute] as $key => $inserted_value) {
									if($inserted_value = $value){
										$is_already_included = true;
										break;
									}
								}

								if($is_already_included == false){
									array_push($Modify_Entry_List[$attribute], $value);

									//indicate there are multiple entries of at least 1 attribute for this email
									if(!isset($Duplicate_Attribute_Values_list[$email])){
										$Duplicate_Attribute_Values_list[$email] = true;
									}
									//indicate there are multiple entries for this email,attribute pair
									if(!isset($Duplicate_Attributes[$attribute][$email])) {
										$Duplicate_Attributes[$attribute][$email] = true;
									}
								}
								else{
									//no need to include 
								}
							}
							else{
								//there is no value for this entry
								//is not a duplicate then
								$Modify_Entry_List[$attribute] = $value;
							}
						}
					}
					else{
						$Modify_Entry_List[$email] = $changing_attributes;
					}

				}
				else{

				}
				if(isset($New_Entry_List[$email])) {
					//NEED TO HANDLE DUPLICATES, MERGE ALL NON DUPLICATE ATTRIBUTE DATA, DISPLAY DUPLICATES
					return true;
				}
				else{
					$New_Entry_List[$email] = $changing_attributes;
					return true;
				}
			}


		}

	}

?>