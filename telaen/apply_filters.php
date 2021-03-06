<?php

defined('I_AM_TELAEN') or die('Direct access not permitted');

if($folder == "inbox") {

	$require_update = false;

	$filename = $userfolder."_infos/filters.ucf";
	$myfile = $UM->_read_file($filename);
	$filters = Array();

	if($myfile != "") 
		$filters = unserialize(base64_decode($myfile));

	/*
	
	examples:
	
	$filters = Array( 
		Array(
				"type"		=> 1,
				"field"		=> 2,
				"match"		=> "filtering",
				"moveto"	=> "Test"
				),
		Array(
				"type"		=> 2,
				"field"		=> 2,
				"match"		=> "ok",
				)
	);
	*/

	// avoid to lose time when there are no filters
	if (count($filters) > 0) {
		
		foreach($headers as $index => $message) {
			foreach($filters as $filter) {
				$match_text = "";
	
				switch($filter["field"]) {
				case FL_FIELD_FROM:
					foreach($message["from"] as $field) {
						$match_text .= " ".$field["name"]." ".$field["mail"];
					}
					break;
				case FL_FIELD_SUBJECT:
					$match_text = " ".$message["subject"];
					break;
				case FL_FIELD_TO:
					foreach($message["to"] as $field) {
						$match_text .= " ".$field["name"]." ".$field["mail"];
					}
					break;
				}
	
				if(!empty($match_text) && strpos($match_text,$filter["match"]) > 0) {
					switch($filter["type"]) {
					case FL_TYPE_MOVE:
	
						$UM->mail_move_msg($message,$filter["moveto"]);
						unset($sess["headers"][base64_encode(strtolower($folder))]);
						unset($sess["headers"][base64_encode(strtolower($filter["moveto"]))]);
	
						$require_update = true;
	
						break;
					case FL_TYPE_DELETE:
	
						$UM->mail_delete_msg($message,$prefs["save-to-trash"],$prefs["st-only-read"]);
						unset($sess["headers"][base64_encode(strtolower($folder))]);
						unset($sess["headers"][base64_encode("trash")]);
	
						$require_update = true;
	
						break;
					case FL_TYPE_MARK_READ:
	
						if(!preg_match('|SEEN|i',$message["flags"])) {
							$UM->mail_set_flag($message,"SEEN","+");
							$sess["headers"][base64_encode(strtolower($folder))][$index] = $message;
						}
	
						break;
					}
				}
	
			}
		}
		reset($headers);
	}
}
?>
