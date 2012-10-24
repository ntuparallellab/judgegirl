<?php
session_start();
if (!isset($_SESSION["ID"]))
    exit("Please <a href='index.htm'>login</a> first.<br>");

header("Cache-Control: no-cache, must-revalidate");

if($_REQUEST["ACTION"] == "UPLOAD"){		# file uploaded

    header("HTTP/1.1 204 No Content");

    if(isset($_SESSION["LOADFILE"])){
	$tmpfile = $_SESSION["LOADFILE"];
	unset($_SESSION["LOADFILE"]);
    }else
	$tmpfile = tempnam("", $_SESSION["ID"]."-");

    $field = $_REQUEST["FIELD"];

    if($_FILES[$field]["size"] > $_REQUEST["MAX_FILE_SIZE"])
	$_FILES[$field]["error"] = UPLOAD_ERR_FORM_SIZE;
    if($_FILES[$field]["error"]){
	$handle = fopen($tmpfile, "a");
	fwrite($handle, "File load error: ");
	switch($_FILES[$field]["error"]){
	    case UPLOAD_ERR_INI_SIZE:
	    case UPLOAD_ERR_FORM_SIZE:
		fwrite($handle, "How do you write such a large program?");
		break;
	    case UPLOAD_ERR_NO_FILE:
		fwrite($handle, "Did you select any file?");
		break;
	    default:
		fwrite($handle, "System error. Please contact the administrator.");
	}
	fclose($handle);
    }else
	move_uploaded_file($_FILES[$field]["tmp_name"], $tmpfile);

    $_SESSION["LOADFILE"] = $tmpfile;

}else if($_REQUEST["ACTION"] == "QUERY"){

    if(isset($_SESSION["LOADFILE"])){		# file ready
	readfile($_SESSION["LOADFILE"]);
	unlink($_SESSION["LOADFILE"]);
	unset($_SESSION["LOADFILE"]);
    }else					# file not ready
	header("HTTP/1.1 204 No Content");
}
?>
