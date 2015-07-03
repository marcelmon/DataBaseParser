<?php


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
					}
				}
			}
			else{
				//no rows :S
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

							case "radio"|"checkboxgroup"|"select"|"checkbox":

								if(in_array($value, $attribute_list[$attribute]['allowed_values'])) {
									$good_attributes[$attribute] = $value;
								}
								else{
									//not a good attribute
								}

							case 'date':

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
				if($result){
					
					if(!isset($user_result['id'])){
						///.SHOULDNT
					}

					foreach ($good_attributes as $attribute => $value) {
						$attribute_query = sprintf("select * from %s where primary key = %s", $GLOBALS['tables']['user_attribute'], $attribut_list['id'].$user_result['id']);
						if(isset($attribute_query)) {
							if($attribute_query['value'] != $value) {
								$changing_attributes[$attribute] = $value;
							}
						}
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