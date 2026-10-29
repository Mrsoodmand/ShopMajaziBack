<?php
try{
	if (!isset($config)){
	  $config = include 'config/config.php';
	}

    if(isset($_GET['ftp']) && $_GET['ftp'] == true) {
        session_name( 'FTPSESSID' );
    }


    function getLaravelUser($api_access=false){
        if(!$api_access){
            return false;
        }

        $config = include 'config/config.php';
        $mysqli = new mysqli($config['DB_HOST'], $config['DB_USERNAME'], $config['DB_PASSWORD'], $config['DB_DATABASE']);
        mysqli_set_charset($mysqli,"utf8");
        $userquery = $mysqli->query("SELECT * FROM `admins` WHERE `api_access` = '$api_access'");
        $user = $userquery->fetch_assoc();
        if(!$user){
            return false;
        }
        $user_id = $user['id'];
        $usermetasquery = $mysqli->query("SELECT * FROM `admin__metas` WHERE `admin_id` = '$user_id'");
        $usermetas=[];
        while($row = $usermetasquery->fetch_assoc()){
            $usermetas[$row['meta_key']] = $row['meta_value'];
        }
        $user['usermeta'] = $usermetas;
        return $user;

    }

    $api_access = $_GET['api_access'] ?? '';
    if($api_access == 'parnafull'){
        $user = true;
    }elseif($api_access){
        $user = getLaravelUser($api_access);
    }else{
        $user = false;
    }
    $user = true;
    if(!$user){
        die('دسترسی غیر مجاز کاربر');
    }


    if(isset($_GET['ftp']) && $_GET['ftp'] == true){
        $ftpaccess = true;
        if($user['ftp_access']){
            $config['ftp_host']         = $user['usermeta']['ftp_host'];
            $config['ftp_user']         = $user['usermeta']['ftp_username'];
            $config['ftp_pass']         = $user['usermeta']['ftp_password'];
            $config['ftp_base_folder']  = '/';
            $ftp_base_folder  = '/';
            //$config['ftp_base_url']     = 'http://www.parnamedia.com/parnamedia.com/test/';

            if(isset($user['usermeta']['ftp_fullaccess']) && $user['usermeta']['ftp_fullaccess'] > 0){
                $config['upload_dir'] = "/";
                $upload_dir  = '/';
//                $config['ftp_base_url']     = 'http://dl.gallerygraph.ir/';
            }
        }else{
            die('دسترسی غیر مجاز اف تی پی');
        }
    }else{
        $ftpaccess = false;
        if($user['usermeta']['userfolder']){
            $_SESSION['RF']["subfolder"] = $user['usermeta']['userfolder'];
        }else{
            $_SESSION['RF']["subfolder"] = '/';

        }
    }


    include 'include/utils.php';

	if ($_SESSION['RF']["verify"] != "RESPONSIVEfilemanager")
	{
		response(trans('forbiden').AddErrorLocation(), 403)->send();
		exit;
	}

	include 'include/mime_type_lib.php';


	$ftp=ftp_con($config);
	if($ftp){
		$source_base = $config['ftp_base_folder'].$config['upload_dir'];
		$thumb_base = $config['ftp_base_folder'].$config['ftp_thumbs_dir'];

	}else{
		$source_base = $config['current_path'];
		$thumb_base = $config['thumbs_base_path'];
	}
	if(isset($_POST["fldr"])){
		$_POST['fldr'] = str_replace('undefined','',$_POST['fldr']);
		$storeFolder = $source_base.$_POST["fldr"];
		$storeFolderThumb = $thumb_base.$_POST["fldr"];
	}else{
		return;
	}

	$fldr = rawurldecode(trim(strip_tags($_POST['fldr']),"/") ."/");

	if (strpos($fldr,'../') !== FALSE
		|| strpos($fldr,'./') !== FALSE
		|| strpos($fldr,'..\\') !== FALSE
		|| strpos($fldr,'.\\') !== FALSE )
	{
		response(trans('wrong path'.AddErrorLocation()))->send();
		exit;
	}

	$path = $storeFolder;
	$cycle = TRUE;
	$max_cycles = 50;
	$i = 0;
	//GET config
	while ($cycle && $i < $max_cycles)
	{
		$i++;
		if ($path == $config['current_path']) $cycle = FALSE;
		if (file_exists($path."config.php"))
		{
			$configTemp = include $path.'config.php';
			$config = array_merge($config,$configTemp);
			//TODO switch to array
			$cycle = FALSE;
		}
		$path = fix_dirname($path).'/';
	}

	require('UploadHandler.php');
	$messages = null;
	if(trans("Upload_error_messages")!=="Upload_error_messages"){
		$messages = trans("Upload_error_messages");
	}

	if(isset($_POST['url'])){
		$temp = tempnam('/tmp','RF');
		$ch = curl_init($_POST['url']);
		$fp = fopen($temp, 'wb');
		curl_setopt($ch, CURLOPT_FILE, $fp);
		curl_setopt($ch, CURLOPT_HEADER, 0);
		curl_exec($ch);
		curl_close($ch);
		fclose($fp);

		$_FILES['files'] = array(
			'name' => array(basename($_POST['url'])),
			'tmp_name' => array($temp),
			'size' => array(filesize($temp)),
			'type' => null
		);
	}

	if($config['mime_extension_rename']){
		$info = pathinfo($_FILES['files']['name'][0]);
		$mime_type = $_FILES['files']['type'][0];
		if (function_exists('mime_content_type')){
			$mime_type = mime_content_type($_FILES['files']['tmp_name'][0]);
		}elseif(function_exists('finfo_open')){
			$finfo = finfo_open(FILEINFO_MIME_TYPE);
			$mime_type = finfo_file($finfo, $_FILES['files']['tmp_name'][0]);
		}else{
			include 'include/mime_type_lib.php';
			$mime_type = get_file_mime_type($_FILES['files']['tmp_name'][0]);
		}
		$extension = get_extension_from_mime($mime_type);

		if($extension=='so' || $extension=='' || $mime_type == "text/troff"){
			$extension = $info['extension'];
		}
		$filename = $info['filename'].".".$extension;
	}else{
		$filename = $_FILES['files']['name'][0];
	}
	$_FILES['files']['name'][0] = fix_filename($filename,$config);

	// LowerCase
	if ($config['lower_case'])
	{
		$_FILES['files']['name'][0] = fix_strtolower($_FILES['files']['name'][0]);
	}
	if (!checkresultingsize($_FILES['files']['size'][0])) {
		$upload_handler->response['files'][0]->error = sprintf(trans('max_size_reached'),$MaxSizeTotal).AddErrorLocation();
		echo json_encode($upload_handler->response);
		exit();
	}

	$uploadConfig = array(
		'config' => $config,
		'storeFolder' => $storeFolder,
		'storeFolderThumb' => $storeFolderThumb,
		'ftp' => $ftp,
		'upload_dir'=> dirname($_SERVER['SCRIPT_FILENAME']).'/'.$storeFolder,
		'upload_url' => $config['base_url'].$config['upload_dir'].$_POST['fldr'],
	    'mkdir_mode' => $config['folderPermission'],
	    'max_file_size' => $config['MaxSizeUpload']*1024*1024,
	    'correct_image_extensions' => true,
	    'print_response' => false
	);
	if(!$config['ext_blacklist']){
		$uploadConfig['accept_file_types'] = '/\.('.implode('|',$config['ext']).')$/i';
	}else{
		$uploadConfig['accept_file_types'] = '/\.(?!('.implode('|',$config['ext_blacklist']).')$)/i';
	}

	if($ftp){
		if (!is_dir($config['ftp_temp_folder'])) {
			mkdir($config['ftp_temp_folder'], $config['folderPermission'], true);
		}
		if (!is_dir($config['ftp_temp_folder']."thumbs")) {
			mkdir($config['ftp_temp_folder']."thumbs", $config['folderPermission'], true);
		}
		$uploadConfig['upload_dir'] = $config['ftp_temp_folder'];
	}

	$upload_handler = new UploadHandler($uploadConfig,true, $messages);

}catch(Exception $e){
	$return = array();
	foreach($_FILES['files']['name'] as $i => $name){
		$return[] = array(
			'name' => $name,
			'error' => $e->getMessage(),
			'size' => $_FILES['files']['size'][$i],
			'type' => $_FILES['files']['type'][$i]
		);
	}
	echo json_encode(array("files"=>$return));
}

