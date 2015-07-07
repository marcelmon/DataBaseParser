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
			//get all attributes and their info
			$query = sprintf('select * from %s', $GLOBALS['tables']['attribute']);
			$attribute_data_rows = Sql_Query($query);	

			if($attribute_data_rows) {
				$attribute_list = array();

				foreach ($attribute_data_rows as $key => $attribute_data) {
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

			//0 if there are no attributes, is only existence
			if(count($entry) == 0) {
				//if there is a user then already done
				if($user_result){
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
			//if there are attributes, must check each value to look for update
			foreach ($entry as $attribute => $new_attribute_value) {

				if(isset($attribute_list[$attribute])) {
					if(isset($user_result['id'])) {
						$attribute_query = sprintf("select * from %s where primary key = %s", $GLOBALS['tables']['user_attribute'], $attribute_list[$attribute]['id'].$user_result['id']);
						$current_user_attribute = Sql_Query($attribute_query);
					}


					//these are single choice values
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

					//these are multiple choice types, the new attribute value must match
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
			$New_Entires_Number_Of_Blocks = $New_Enties_Total_Amount/$Current_New_Entries_Display_Amount + (($New_Enties_Total_Amount % $New_Enties_Total_Amount)? 1:0);
		
			$Current_New_Entry_Block_Number = 0;

			$Current_New_Entry_Block = array_slice($New_Entry_List, $Current_New_Entry_Block_Number*$Current_New_Entries_Display_Amount, $Current_New_Entries_Display_Amount);
		
			$HTML_Display_Text = sprintf('<form name="New_Entry_Submit_Form_Block__%d" action="%s" method="post">', $current_block_number, 'self');

			$HTML_Display_Text = $HTML_Display_Text.sprintf('<table id="New_User_Attribute_Select_Table_Block__%d">', $current_block_number);

			$HTML_table_row = '<tr><td>EMAIL</td>'
			foreach ($attribute_list as $attribute_name => $attribute_type) {
				$HTML_table_row = $HTML_table_row.sprintf('<td>%s<input type="CHECKBOX" name="Attribute_Column_Select[%s]" value="checked"',$attribute_name, $attribute_name);
			}
			$HTML_Display_Text = $HTML_Display_Text.'</tr>';

			foreach ($Current_New_Entry_Block as $email_key => $new_user_attributes_and_values) {

				if(isset($Commited_New_Entires[$email_key]) {
					$HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" name="Modify_Entry_List[%s][\'include\']" value="include" checked>Include This Email</input></td>',$email_key, $email_key);
				}

				else{
					$HTML_table_row = sprintf('<tr><td>%s<br><input type="checkbox" name="Modify_Entry_List[%s][\'include\']" value="include">Include This Email</input></td>',$email_key, $email_key);
				}

				//commited_new_entries[email]: attribute,value
				foreach ($attribute_list as $attribute_name => $attribute_type) {

					if(!isset($new_user_attributes_and_values[$attribute_name])) {
						$HTML_table_row = $HTML_table_row.'<td></td>';
					}

					else if(isset($Commited_New_Entires[$email_key] && isset($Commited_New_Entires[$email_key][$attribute_name]))) {

						if($attribute_type == 'checkboxgroup') {
							$selectedGroupValues = split(',', $Commited_New_Entires[$email_key][$attribute_name]);
						}

						//one of the possible new attribute values for this attribute is already checked

						//these are all possible attribute values
						//for single values, 
						foreach ($new_user_attributes_and_values[$attribute_name] as $key => $attribute_value) {
							//must display each of the possible attribute values in a single cell

							$HTML_table_row= $HTML_table_row.'<td>';


							switch($attribute_type){

								case "textarea"|"textline"|"checkbox"|"hidden"|"date": 

									if($Commited_New_Entires[$email_key][$attribute_name] == $attribute_value) {
										//if the attribute value is the already selected, mark as checked
										$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s" checked>%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);

									}
									else{
										//else not yet selected so just create the input
										$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
									}

									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';
									break;
								

								case "checkboxgroup": 
									
									if(in_array($attribute_value, $selectedGroupValues)) {
										//the current attribute value should already be checked

										$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s" checked>%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
									}
									else{
										//not already checked
										$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s">%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);
									}
									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';

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


							switch($attribute_type){

								case "textarea"|"textline"|"checkbox"|"hidden"|"date": 

									$HTML_attribute_value_input = sprintf('<input type="radio" name="Modify_Entry_List[%s][%s]" value="%s">%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);

									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';
									break;
								

								case "checkboxgroup": 
									
									$HTML_attribute_value_input = sprintf('<input type="checkbox" name="Modify_Entry_List[%s][%s][]" value="%s">%s</input>', $email_key, $attribute_name, $attribute_value, $attribute_value);

									$HTML_table_row= $HTML_table_row.$HTML_attribute_value_input.'<br>';

									break;
							}
							
							
						}
						//have cycled through each of possible new values for the attribute
						$HTML_table_row = $HTML_table_row.'</td>';
					}
				}

				$HTML_Display_Text = $HTML_Display_Text.$HTML_table_row;
				
			}
			$HTML_Display_Text = $HTML_Display_Text.'</table>';
			$HTML_submit_buttons = '<input type="submit" value="Submit_all"></input><input type="submit" value="New_Entries_Table_Next_page"></input><input type="submit" value="New_Entries_Table_Previous_Page"></input></form>';

			$HTML_Display_Text = $HTML_Display_Text.$HTML_submit_buttons;

			return $HTML_Display_Text;
		}


		function Display_New_Entry_Table_Block()




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