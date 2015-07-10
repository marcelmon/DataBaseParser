<?php

	require_once dirname(__FILE__).'/commonlib/lib/userlib.php';
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
				$AttributeChangerPluggin = $GLOBALS['tables']['pluggins']['AttributeChanger'];
			}
			$tables=$GLOBALS['tables'];
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
				$tables=$GLOBALS['tables'];
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

		function Initialize_Attribute_Changer() {
			//get all attributes and their info
			$query = sprintf('select * from %s', $GLOBALS['tables']['attribute']);
			$attribute_data_return = Sql_Query($query);	


			if($attribute_data_return) {
				$attribute_list = array();

				while($attribute_data = Sql_fetch_array($attribute_data_return)) {
					if(!isset( ($attribute_data['id']) | ($attribute_data['name']) | ($attribute_data['type']) )) {
						//not known format, cannot use
					}
					else{
						//use the attribute list to get type and value information
						$attribute_list[$attribute_data['name']] = $attribute_data;
						if($attribute_data['type'] == ("radio"|"checkboxgroup"|"select"|"checkbox")) {
							if(!isset($attribute_data['tablename'])) {

							}
							else{
								//must query to get the allowed values
								$value_table_name = $table_prefix."listattr_".$attribute_data["tablename"];
								$value_query = sprintf("select name from %s", $value_table_name);
								$allowed_values_res = Sql_Query($value_query);


								if($allowed_value_res) {
									while($row = Sql_Fetch_Row_Query($allowed_values_res)) {
										array_push($attribute_list[$attribute_data['name']]['allowed_values'], $row['name']);
									}
								}
								else{
									unset($attribute_list[$attribute_data['name']]['allowed_values']);
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

		}
		
		function Updated_Test_Entry($entry) {
			//entry is [email]=>array (attribute, value)

			$changing_attributes = array();

			if(!array_key_exists("email", $entry)) {
				return false;
			}
			$email = $entry['email'];
			array_shift($entry);

			if(!filter_var($email, FILTER_VALIDATE_EMAIL) ){
				return false;
			}

			$entry_query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email);
			$user_sql_result = Sql_Query($entry_query);

			//0 if there are no attributes, is only existence
			if(!is_array($entry) == 0) {
				//if there is a user then already done
				if($user_sql_result){
					return true;
				}
				else{
					//will need to create a new user if not already
					if(isset($New_Entry_List[$email])) {
						return true;
					}
					else{
						$New_Entry_List[$email] = array();
						return true;
					}
				}
			}
			$user_result = Sql_fetch_array($user_sql_result);

			//if there are attributes, must check each value to look for update
			foreach ($entry as $attribute => $new_attribute_value) {

				if(isset($attribute_list[$attribute])) {
					if(isset($user_result['id'])) {
						$attribute_query = sprintf("select value from %s where attributeid = %s and userid = %s", $GLOBALS['tables']['user_attribute'], $attribute_list[$attribute]['id'], $user_result['id']);
						$current_attribute_return = Sql_Fetch_Row_Query($attribute_query);
						if(!$current_attribute_return) {
							unset($current_user_attribute);
						}
						else{
							$current_user_attribute = $current_attribute_return[0];
						}
					}


					//these are single choice values
					if($attribute_list[$attribute]['type'] == "radio"|"select"|'checkbox') {

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

					//these are multiple choice types, the new attribute value must match
					else if($attribute_list[$attribute]['type'] == 'checkboxgroup') {

						$exploded_attribute_values_array = explode(',', $new_attribute_value);

						$has_attributes = false;
						if(isset($user_result['id'])) {

							if($current_user_attribute != null) {
								$current_user_attribute_value_id_array = explode(',', $current_user_attribute);
								foreach ($current_user_attribute_value_id_array as $key => $attribute_value_id) {
									$attribute_value_query = sprintf("select name from %s where id = %d", $attribute_list[$attribute]['tablename'], $attribute_value_id);
									$attribute_value_return = Sql_Query($attribute_value_query);
									if($attribute_value_return) {
										array_push($current_user_attribute_array, Sql_Fetch_Row($attribute_value_return)[0]);
									}
								}
								$has_attributes = true;
							}
						}

						foreach ($exploded_attribute_values_array as $key => $exploded_attribute_value) {

							if(in_array($exploded_attribute_value, $attribute_list[$attribute]['allowed_values'])) {

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

//////////////////////////////////////

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

		$Commited_New_Entires = array();

		$Commited_Modify_Entries = array();


		//either 10, 100, 1000, 10000, all
		//default 100
		$Current_New_Entries_Display_Amount;

		$New_Enties_Total_Amount;

		$New_Entires_Number_Of_Blocks;

		$Current_New_Entry_Block_Number;

		$Current_New_Entry_Block;



		function Initialize_New_Entries_Display() {

			$Current_New_Entries_Display_Amount = 100;

			$New_Enties_Total_Amount = count($New_Entry_List);

			$New_Entires_Number_Of_Blocks = $New_Enties_Total_Amount/$Current_New_Entries_Display_Amount + (($Current_New_Entries_Display_Amount % $New_Enties_Total_Amount)? 1:0);
		
			$Current_New_Entry_Block_Number = 0;

			//need to finish this
			
		}

		function New_Entry_Change_Display_Amount($New_Amount) {
			if($New_Amount != (10|100|1000|10000)) {
				return false;
			}
			$Current_New_Entries_Display_Amount = $New_Amount;

			$New_Entires_Number_Of_Blocks = $New_Enties_Total_Amount/$Current_New_Entries_Display_Amount + (($Current_New_Entries_Display_Amount % $New_Enties_Total_Amount)? 1:0);

			$Current_New_Entry_Block_Number = 0;
			return true;
		}

		function New_Entry_Display_Next_Page() {
			if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks-1) {
				$Current_New_Entry_Block_Number++;
				return Get_Modify_Entry_Table_Block($Current_New_Entry_Block_Number);
			}
			else{
				//because there are no more blocks
				return false;
			}
		}

		function New_Entry_Display_Previous_Page() {
			if($Current_New_Entry_Block_Number > 0) {
				$Current_New_Entry_Block_Number--;
				return Get_Modify_Entry_Table_Block($Current_New_Entry_Block_Number);
			}
			else{
				//because there are no more blocks
				return false;
			}
		}

		function Get_New_Entry_Table_Block() {

			$Current_New_Entry_Block = array_slice($New_Entry_List, $Current_New_Entry_Block_Number*$Current_New_Entries_Display_Amount, $Current_New_Entries_Display_Amount);


			$HTML_Display_Text = sprintf('<form name="New_Entry_Submit_Form_Block__%d" action="%s" method="post">', $Current_New_Entry_Block_Number, 'self');

			$HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="New_User_Attribute_Select_Table_Block__%d">', $Current_New_Entry_Block_Number);

			$HTML_table_row = '<tr><td>EMAIL</td>';

			foreach ($attribute_list as $attribute_name => $attribute_info) {
				$HTML_table_row = $HTML_table_row.sprintf('<td>%s<input type="CHECKBOX" name="New_Entry_Attribute_Column_Select[%s]" value="checked">',$attribute_name, $attribute_name);
			}

			$HTML_Display_Text = $HTML_Display_Text.'</tr>';

			foreach ($Current_New_Entry_Block as $email_key => $new_user_attributes_and_values) {

				if(isset($Commited_New_Entires[$email_key]) {
					$HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" name="New_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input></td>',$email_key, $email_key);
				}

				else{
					$HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" name="New_Entry_List[%s][\'include\']" value="include">Include This Email</input></td>',$email_key, $email_key);
				}

				//commited_new_entries[email]: attribute,value
				foreach ($attribute_list as $attribute_name => $attribute_info) {

					if(!isset($new_user_attributes_and_values[$attribute_name])) {
						$HTML_table_row = $HTML_table_row.'<td></td>';
					}

					else if(isset($Commited_New_Entires[$email_key] && isset($Commited_New_Entires[$email_key][$attribute_name]))) {

						if($attribute_info['type'] == 'checkboxgroup') {
							$selectedGroupValues = split(',', $Commited_New_Entires[$email_key][$attribute_name]);
						}

						//one of the possible new attribute values for this attribute is already checked

						//these are all possible attribute values
						//for single values, 
						foreach ($new_user_attributes_and_values[$attribute_name] as $key => $attribute_value) {
							//must display each of the possible attribute values in a single cell

							$HTML_table_row= $HTML_table_row.'<td>';


							switch($attribute_info['type']){

								case "textarea"|"textline"|"checkbox"|"hidden"|"date": 

									if($Commited_New_Entires[$email_key][$attribute_name] == $attribute_value) {
										//if the attribute value is the already selected, mark as checked
										$HTML_attribute_value_input = sprintf('<input type="radio" name="New_Entry_List[%s][%s]" value="%s" checked>%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);

									}
									else{
										//else not yet selected so just create the input
										$HTML_attribute_value_input = sprintf('<input type="radio" name="New_Entry_List[%s][%s]" value="%s">%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
									}

									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';
									break;
								

								case "checkboxgroup": 
									
									if(in_array($attribute_value, $selectedGroupValues)) {
										//the current attribute value should already be checked

										$HTML_attribute_value_input = sprintf('<input type="checkbox" name="New_Entry_List[%s][%s][]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $attribute_value, $attribute_value);
									}
									else{
										//not already checked
										$HTML_attribute_value_input = sprintf('<input type="checkbox" name="New_Entry_List[%s][%s][]" value="%s">%s</input><br>', $email_key, $attribute_name, $attribute_value, $attribute_value);
									}
									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';

									break;
								default:
									break;
							}
							
							
						}
						//have cycled through each of possible new values for the attribute
						$HTML_table_row= $HTML_table_row.'</td>';


					}
					else{
						//this attribute has no chosen attribute values set for this email
						foreach ($new_user_attributes_and_values[$attribute_name] as $key => $attribute_value) {
							//must display each of the possible attribute values in a single cell

							$HTML_table_row= $HTML_table_row.'<td>';


							switch($attribute_info['type']){

								case "textarea"|"textline"|"checkbox"|"hidden"|"date": 

									$HTML_attribute_value_input = sprintf('<input type="radio" name="New_Entry_List[%s][%s]" value="%s">%s</input><br>', $email_key, $attribute_name, $attribute_value, $attribute_value);

									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';
									break;
								

								case "checkboxgroup": 
									
									$HTML_attribute_value_input = sprintf('<input type="checkbox" name="New_Entry_List[%s][%s][]" value="%s">%s</input><br>', $email_key, $attribute_name, $attribute_value, $attribute_value);

									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';

									break;
							}
							
							
						}
						//have cycled through each of possible new values for the attribute
						$HTML_table_row = $HTML_table_row.'</td>';
					}
				}

				$HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';
				
			}
			$HTML_Display_Text = $HTML_Display_Text.'</table>';
			$HTML_submit_buttons = '<input type="submit" name="New_Entries_Table_Submit_all" value="New_Entries_Table_Submit_all"></input>';

			if($Current_New_Entry_Block_Number > 0) {
				$HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name="value="New_Entries_Table_Previous_Page" value="New_Entries_Table_Previous_Page"></input>';
			}

			if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks) {
				$HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name="New_Entries_Table_Next_page" value="New_Entries_Table_Next_page"></input>';
			}

			switch($Current_New_Entries_Display_Amount){
				case 10:
					$HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10" checked>10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option>';
				case 100:
					$HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100" checked>100</option><option value="1000">1000</option><option value="10000">10000</option>';
				case 1000:
					$HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000" checked>1000</option><option value="10000">10000</option>';
				case 10000:
					$HTML_Display_Size_Submit = '<select name="New_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000" checked>10000</option>';
			}

			$HTML_Display_Size_Submit = $HTML_Display_Size_Submit.'<input type="submit" name="New_Entry_Change_Display_Amount" value="New_Entry_Change_Display_Amount"></input>';

			$HTML_Display_Text = $HTML_Display_Text.$HTML_submit_buttons.$HTML_Display_Size_Submit.'</form>';

			$HTML_current_table_info = sprintf("Current Block : %d of %d. Displaying %d entires per page.", $Current_New_Entry_Block_Number+1, $New_Entires_Number_Of_Blocks, $Current_New_Entries_Display_Amount);
			$HTML_Display_Text = $HTML_Display_Text.$HTML_current_table_info;

			return $HTML_Display_Text;
		}




		$Current_Modify_Entries_Display_Amount;


		$Current_user_values;

		$Modify_Enties_Total_Amount;

		$Modify_Entires_Number_Of_Blocks;

		$Current_Modify_Entry_Block_Number;


		$Current_Modify_Entry_Block;

		$Commited_Modify_Entries;


		function Initialize_Modify_Entries_Display() {

			$Current_Modify_Entries_Display_Amount = 100;

			$Modify_Enties_Total_Amount = count($Modify_Entry_List);

			$Modify_Entires_Number_Of_Blocks = $Modify_Enties_Total_Amount/$Current_Modify_Entries_Display_Amount + (($Current_Modify_Entries_Display_Amount % $Modify_Enties_Total_Amount)? 1:0);
		
			$Current_Modify_Entry_Block_Number = 0;


			
		}	

		function Modify_Entry_Display_Next_Page() {
			if($Current_Modify_Entry_Block_Number < $Modify_Entires_Number_Of_Blocks-1) {
				$Current_Modify_Entry_Block_Number++;
				return Get_Modify_Entry_Table_Block($Current_Modify_Entry_Block_Number);
			}
			else{
				//because there are no more blocks
				return false;
			}
		}

		function Modify_Entry_Display_Previous_Page() {
			if($Current_Modify_Entry_Block_Number > 0) {
				$Current_Modify_Entry_Block_Number--;
				return Get_Modify_Entry_Table_Block($Current_Modify_Entry_Block_Number);
			}
			else{
				//because there are no more blocks
				return false;
			}
		}

		function Modify_Entry_Change_Display_Amount($New_Amount) {
			if($New_Amount != (10|100|1000|10000)) {
				return false;
			}
			$Current_Modify_Entries_Display_Amount = $New_Amount;

			$Modify_Entires_Number_Of_Blocks = $Modify_Enties_Total_Amount/$Current_Modify_Entries_Display_Amount + (($Current_Modify_Entries_Display_Amount % $Modify_Enties_Total_Amount)? 1:0);

			$Current_New_Entry_Block_Number = 0;
			return true;
		}

		function Get_Modify_Entry_Table_Block() {

			$Current_Modify_Entry_Block = array_slice($Modify_Entry_List, $Current_Modify_Entry_Block_Number*$Current_Modify_Entries_Display_Amount, $Current_Modify_Entries_Display_Amount);


			$HTML_Display_Text = sprintf('<form name="Modify_Entry_Submit_Form_Block__%d" action="%s" method="post">', $Current_Modify_Entry_Block_Number, 'self');

			$HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="Modify_User_Attribute_Select_Table_Block__%d">', $Current_Modify_Entry_Block_Number);

			$HTML_table_row = '<tr><td>EMAIL</td>';

			foreach ($attribute_list as $attribute_name => $attribute_info) {
				$HTML_table_row = $HTML_table_row.sprintf('<td>%s<input type="CHECKBOX" name="Modify_Entry_Attribute_Column_Select[%s]" value="checked">',$attribute_name, $attribute_name);
			}

			$HTML_Display_Text = $HTML_Display_Text.'</tr>';

			foreach ($Current_Modify_Entry_Block as $email_key => $modify_user_attributes_and_values) {

				if(!isset($Current_user_values[$email_key])) {

					$user_query = sprintf('select id from %s where email = %s', $GLOBALS['tables']['user'], $email_key);

					$user_result = Sql_Query($user_query);
					if(!$user_query) {
						//should add the entry to new_entry_block
					}
					else{
						$Current_user_values[$email_key] = array();
						$Current_user_values[$email_key]['user_id'] = $user_result['id'];
						$Current_user_values[$email_key]['attributes'] = array();

						foreach ($attribute_list as $attribute_name => $attribute_info) {

							$current_attribute_value_query = sprintf('select value from %s where primary key = %s', $GLOBALS['tables']['user_attribute'], $user_result['id'].$attribute_info['id']);
							$current_attribute_value = Sql_Query($current_attribute_value_query);
							if(isset($current_attribute_value)) {
								if($current_attribute_value != '') {
									$Current_user_values[$email_key]['attributes'][$attribute_name] = $current_attribute_value;
								}
							}
						}
					}
				}
				
				if(isset($Commited_Modify_Entries[$email_key])) {
					$HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" name="Modify_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input></td>',$email_key, $email_key);
				}
				else{
					$HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" name="Modify_Entry_List[%s][\'include\']" value="include">Include This Email</input></td>',$email_key, $email_key);
				}
				
				foreach ($attribute_list as $attribute_name => $attribute_info) {
					$HTML_table_row = $HTML_table_row.'<td>';
					
					if(isset($Current_user_values[$email_key]['attributes'][$attribute_name])) {

						if(isset($Commited_Modify_Entries[$email_key] && isset($Commited_Modify_Entries[$email_key][$attribute_name]))) {

							if($attribute_info['type'] == 'checkboxgroup') {
								$selectedGroupValues = split(',', $Commited_Modify_Entires[$email_key][$attribute_name]);
								$currentlySetGroupValues = split(',', $Current_user_values[$email_key]['attributes'][$attribute_name]);
								foreach ($currentlySetGroupValues as $key => $group_attribute_value) {
									//these are the current set values, should make green background or w/e
									if(in_array($group_attribute_value, $selectedGroupValues)) {
										$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $group_attribute_value, $group_attribute_value);
									}
									else {
										$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s">%s</input><br>', $email_key, $attribute_name, $group_attribute_value, $group_attribute_value);
									}
									$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
								}
							}
							else{
								//will only be one currently set value
								if($Current_user_values[$email_key]['attributes'][$attribute_name] == $Commited_Modify_Entries[$email_key][$attribute_name]) {
									$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $Current_user_values[$email_key]['attributes'][$attribute_name], $Current_user_values[$email_key]['attributes'][$attribute_name]);
								}
								else {
									$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $email_key, $attribute_name, $Current_user_values[$email_key]['attributes'][$attribute_name], $Current_user_values[$email_key]['attributes'][$attribute_name]);
								}
								$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
							}

						}
						//no need to check this attribute for selected
						else {
							if($attribute_info['type'] == 'checkboxgroup') {
								$currentlySetGroupValues = split(',', $Current_user_values[$email_key]['attributes'][$attribute_name]);
								foreach ($currentlySetGroupValues as $key => $group_attribute_value) {
									$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $group_attribute_value, $group_attribute_value);
								}
								$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
							}
							else{
								$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $Current_user_values[$email_key]['attributes'][$attribute_name], $Current_user_values[$email_key]['attributes'][$attribute_name]);
								$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
							}
							
						}

					}
					else{

					}
					$HTML_table_row = $HTML_table_row.'</td>';

				}
				$HTML_table_row = $HTML_table_row.'</tr>';
				$HTML_Display_Text = $HTML_Display_Text.$HTML_table_row;
				//now must check the modify choices
				$HTML_table_row = '<tr><td></td>'
				foreach ($attribute_list as $attribute_name => $attribute_info) {

					$HTML_table_row = $HTML_table_row.'<td>';

					if(!isset($Current_Modify_Entry_Block[$email_key][$attribute_name])) {
						
					}

					else if(isset($Commited_Modify_Entries[$email_key] && isset($Commited_Modify_Entries[$email_key][$attribute_name]))) {

						if($attribute_info['type'] == 'checkboxgroup') {
							$selectedGroupValues = split(',', $Commited_Modify_Entires[$email_key][$attribute_name]);
							foreach ($Current_Modify_Entry_Block[$email_key][$attribute_name] as $key => $group_attribute_value) {
								//these are the current set values, should make green background or w/e
								if(in_array($group_attribute_value, $selectedGroupValues)) {
									$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $group_attribute_value, $group_attribute_value);
								}
								else {
									$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s">%s</input><br>', $email_key, $attribute_name, $group_attribute_value, $group_attribute_value);
								}
								$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
							}
						}
						else{
							//will only be one currently set value
							if($Current_Modify_Entry_Block[$email_key][$attribute_name] == $Commited_Modify_Entries[$email_key][$attribute_name]) {
								$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input><br>', $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
							}
							else {
								$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
							}
							$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
						}
					}
					else{
						if($attribute_info['type'] == 'checkboxgroup') {

							foreach ($Current_Modify_Entry_Block[$email_key][$attribute_name] as $key => $group_attribute_value) {
								
								$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s">%s</input><br>', $email_key, $attribute_name, $group_attribute_value, $group_attribute_value);
								
								$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
							}
						}
						else{
							//will only be one currently set value
							
							$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input><br>', $email_key, $attribute_name, $Current_Modify_Entry_Block[$email_key][$attribute_name], $Current_Modify_Entry_Block[$email_key][$attribute_name]);
							$HTML_table_row = $HTML_table_row.$HTML_attribute_value_input;
						}
					}
					$HTML_table_row = $HTML_table_row.'</td>';
				}
				$HTML_Display_Text = $HTML_Display_Text.$HTML_table_row.'</tr>';
				
			}

			$HTML_Display_Text = $HTML_Display_Text.'</table>';
			$HTML_submit_buttons = '<input type="submit" name ="Modify_Entries_Table_Submit_all" value="Submit_all"></input>';

			if($Current_New_Entry_Block_Number > 0) {
				$HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Previous_Page" value="Modify_Entries_Table_Previous_Page"></input>';
			}

			if($Current_New_Entry_Block_Number < $New_Entires_Number_Of_Blocks) {
				$HTML_submit_buttons = $HTML_submit_buttons.'<input type="submit" name ="Modify_Entries_Table_Next_page" value="Modify_Entries_Table_Next_page"></input>';
			}

			switch($Current_Modify_Entries_Display_Amount){
				case 10:
					$HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10" checked>10</option><option value="100">100</option><option value="1000">1000</option><option value="10000">10000</option>';
				case 100:
					$HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100" checked>100</option><option value="1000">1000</option><option value="10000">10000</option>';
				case 1000:
					$HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000" checked>1000</option><option value="10000">10000</option>';
				case 10000:
					$HTML_Display_Size_Submit = '<select name="Modify_Entries_New_Display_Amount"><option value="10">10</option><option value="100">100</option><option value="1000">1000</option><option value="10000" checked>10000</option>';
			}

			$HTML_Display_Size_Submit = $HTML_Display_Size_Submit.'<input type="submit" name="New_Entry_Change_Display_Amount" value="New_Entry_Change_Display_Amount"></input>';

			$HTML_Display_Text = $HTML_Display_Text.$HTML_submit_buttons.$HTML_Display_Size_Submit.'</form>';

			$HTML_current_table_info = sprintf("Current Block : %d of %d. Displaying %d entires per page.", $Current_Modify_Entry_Block_Number+1, $Modify_Entires_Number_Of_Blocks, $Current_Modify_Entries_Display_Amount);
			$HTML_Display_Text = $HTML_Display_Text.$HTML_current_table_info;

			return $HTML_Display_Text;
		}


		$Failed_New_Entries;


		function Push_New_Entries() {
			foreach ($Commited_New_Entires as $email_key => $new_attributes_and_values) {
				$exists = Sql_Fetch_Row_Query(sprintf('select id from %s where email = "%s"', $GLOBALS['tables']['user'],$email_key));
				if($exists) {
					$Failed_New_Entries[$email_key] = $new_attributes_and_values;
				}
				else{

					$new_user_id = addNewUser($email_key);
					foreach ($new_attributes_and_values as $this_attribute_name => $this_attribute_value) {
						if($attribute_list[$this_attribute_name]['type'] == 'checkboxgroup') {
							$this_attribute_value = implode(',', $this_attribute_value);
						}
						//need a way for 'STICKY' attributes
						saveUserAttribute($new_user_id, $attribute_list[$this_attribute_name]['id'], $this_attribute_value);
					}	
				}
			}
		}

		$Failed_Modify_Entries;
		function Push_Modify_Entries() {

			foreach ($Commited_Modify_Entries as $email_key => $modify_attributes_and_values) {
				$exists = Sql_Fetch_Row_Query(sprintf('select id from %s where email = "%s"', $GLOBALS['tables']['user'],$email_key));
				if(!$exists) {
					$Failed_Modify_Entries[$email_key] = $modify_attributes_and_values;
				}
				else{
					$modify_user_id = $exists[0];
					foreach ($modify_attributes_and_values as $this_attribute_name => $this_attribute_value) {
						if($attribute_list[$this_attribute_name]['type'] == 'checkboxgroup') {
							$this_attribute_value = implode(',', $this_attribute_value);
						}
						//need a way for 'STICKY' attributes
						saveUserAttribute($new_user_id, $attribute_list[$this_attribute_name]['id'], $this_attribute_value);
					}
				}
			}
		}









//all below need adjustment

		function Set_Display_New_Entires($display_amount) {

			//display amount either 10, 100, 1000

			$total_amount = count($New_Entry_List)

			$number_of_blocks = $total_amount/$display_amount + (($total_amount % $display_amount)? 1:0);

			$current_block_number = 0;

			$current_block = array_slice($New_Entry_List, $current_block_number*$display_amount, $display_amount);

			$HTML_Display_Text = sprintf('<form id="new_entry_submit_form_block_%s" name="" action="%s" method="post">', $current_block_number, 'self');



			$attribute_name_array = array();

			foreach ($attribute_list as $attribute_name => $attribute_type) {
				array_push($attribute_name_array, $attribute_name);
			}


			//still need to set for things like position and areas of practice

			foreach ($current_block as $email_index => $attributes_and_values) {
				$current_index=0;

				$table_row = sprintf('<tr><td>%s</td>', $email_index);
				$has_value = true;
				while(true) {
					if($current_index > 0) {
						$table_row = sprintf('<tr><td></td>');
					}
					foreach ($attribute_name_array as $key => $attribute_name) {

						if(isset($New_Entry_List[$email_index][$attribute_name][$current_index])){

							$table_row = $table_row.sprintf('<td><input type="radio" name="New_Entry_Table[%s][%s]" value="%s"></td>', $email_index, $attribute_name, $New_Entry_List[$email_index][$attribute_name][$current_index]);
							$has_value = true;
						}
						else{
							$table_row = $table_row.sprintf('<td></td>');
						}

					}

					if($has_value == true) {
						if($current_index == 0){
							$HTML_Display_Text = $HTML_Display_Text.$table_row.sprintf('<input type="radio" name="New_Entry_Table[%s][%s]" value="%s" checked></tr>',$email_index,'ALL_FIRST_ROW','ALL_FIRST_ROW_SET');
						}
						else{
							$HTML_Display_Text = $HTML_Display_Text.$table_row.'</tr>';
						}
						$has_value = false;
						$current_index++;
					}
					else{
						break;
					}
				}
			}
			$HTML_Display_Text = $HTML_Display_Text.'</table><input type="submit" value="Submit"></form>';
			return $HTML_Display_Text;

		}

		function Change_Display($new_page_number) {

		}

		function Set_Display_Modify_Entries($display_amount) {
			$total_amount = count($Modify_Entry_List)

			$number_of_blocks = $total_amount/$display_amount + (($total_amount % $display_amount)? 1:0);

			$current_block_number = 0;

			$current_block = array_slice($Modify_Entry_List, $current_block_number*$display_amount, $display_amount);

			$HTML_Display_Text = sprintf('<form id="modify_entry_submit_form_block_%s" name="" action="%s" method="post">', $current_block_number, 'self');

			$HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="attribute_value_table">');

			$attribute_name_array = array();

			foreach ($attribute_list as $attribute_name => $attribute_type) {
				array_push($attribute_name_array, $attribute_name);
			}


			//still need to set for things like position and areas of practice

			foreach ($current_block as $email_index => $attributes_and_values) {
				$entry_query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email_index);
				$user_result = Sql_Query($entry_query);
				$table_row = sprintf('<tr><td>%s</td>', $email_index);

				foreach ($attribute_name_array as $key => $attribute_name) {

					$attribute_query =  sprintf('select value from %s where primary key = "%s"', $GLOBALS['tables']['user_attribute'], $user_result['id'].$attribute_list[$attribute_name]['id']);
					$current_attribute_value = Sql_Query($attribute_query);
					if($current_attribute_value) {
						$table_row = $table_row.sprintf('<tr><td>%s</td>', $current_attribute_value);
					}
					else{
						$table_row = $table_row.'<td></td>';
					}
				}
				$HTML_Display_Text = $HTML_Display_Text.$table_row.'</tr>';

				$has_value = false;
				$current_index = 0;
				while(true) {
					$table_row = '<tr><td></td>';

					foreach ($attribute_name_array as $key => $attribute_name) {

						if(isset($Modify_Entry_List[$email_index][$attribute_name][$current_index])) {
							$table_row = $table_row.sprintf('<td><input type="radio" name="Modify_Entry_Table[%s][%s]" value="%s"></td>', $email_index, $attribute_name, $New_Entry_List[$email_index][$attribute_name][$current_index]);
							$has_value = true;
						}
						else{
							$table_row = $table_row.'<td></td>'
						}
					}
					if($has_value == true) {
						if($current_index == 0){

									//MUST HAVE CLICKING THIS RADIO BUTTON TO SET EACH OTHER RADIO BUTTON
							$HTML_Display_Text = $HTML_Display_Text.$table_row.sprintf('<input type="radio" name="Modify_Entry_Table[%s][%s]" value="%s" checked></tr>',$email_index, "ALL_FIRST_ROW", "ALL_FIRST_ROW");
						}
						else{
							$HTML_Display_Text = $HTML_Display_Text.$table_row.'</tr>';
						}
						$has_value = false;
						$current_index++;
					}
					else{
						break;
					}

				}

			}
			$HTML_Display_Text = $HTML_Display_Text.'</table><input type="submit" value="Submit"></form>';
			return $HTML_Display_Text;
		}

		$Stored_New_entries;
		$Stored_Modify_Entries;

		function Submit_New_Entries(){
			//store all new entries

			foreach ($Stored_New_entries as $email => $attributes) {
				$entry_query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email);
				$user_result = Sql_Query($entry_query);
				if($user_result){
					//skip, shouldnt have already found one
				}
				else{
					$id_val = $GLOBALS['incremental_id']++;
					$new_entry_query = sprintf('insert into %s (id,email) values("%s","%s")', $GLOBALS['tables']['user'], $id_val, $email);
					Sql_Query($new_entry_query);
///////////////////THERE IS PROBABLY NEW USER FUNCTIONS TO USE/////////////////////////////////////////
					foreach ($attributes as $attribute_name => $attribute_info) {
						if(!isset($attribute_list[$attribute_name])) {
							//skip
						}
						else{
							$new_entry_attribute_query = sprintf('insert into %s ()')
						}
					}
				}
			}
			
		}

		function Submit_Modify_Entries() {

			foreach ($Stored_Modify_Entries as $email => $attribute_values) {
				$entry_query = sprintf('select * from %s where email = "%s"', $GLOBALS['tables']['user'], $email);
				$user_result = Sql_Query($entry_query);
				if(isset($user_result['id'])) {
					foreach ($attribute_values as $attribute_name => $attribute_value) {
						$update_query = sprintf('update %s set value="%s" where primary key="%s"', $GLOBALS['tables']['user_attribute'], $attribute_value, $attribute_list[$attribute_name]['id'].$user_result['id']);
						Sql_Query($update_query);
					}
				}
			}

		}

		/////////CHECKBOXES HAVE COLUMNS AS VALUE EITHER ON OR OFF for values


		if(isset($_POST[$id])){
			if(isset($_POST['New_Entry_Table'])) {

				foreach ($_POST['New_Entry_Table'] as $email => $attribute_value_set) {

					$Stored_New_entries[$email] = array();
					if(isset($attribute_value_set['ALL_FIRST_ROW']) {
						foreach ($New_Entry_List[$email] as $attribute_name => $values_array) {
						 	$Stored_New_entries[$email][$attribute_name] = $values_array[0];
						}
					}
					else{
						foreach ($attribute_value_set as $attribute_name => $attribute_value) {
							$Stored_New_entries[$email][$attribute_name] = $attribute_value;
						}
					}
				}
			}
			if(isset($_POST['Modify_Entry_Table'])) {

				foreach ($_POST['Modify_Entry_Table'] as $email => $attribute_value_set) {

					$Stored_Modify_Entries[$email] = array();
					if(isset($attribute_value_set['ALL_FIRST_ROW']) {
						foreach ($New_Entry_List[$email] as $attribute_name => $values_array) {
						 	$Stored_Modify_Entries[$email][$attribute_name] = $values_array[0];
						}
					}
					else{
						foreach ($attribute_value_set as $attribute_name => $attribute_value) {
							$Stored_Modify_Entries[$email][$attribute_name] = $attribute_value;
						}
					}
				}
			}
		}

	}



	

?>