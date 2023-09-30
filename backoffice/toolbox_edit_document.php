<?php

require_once('includes/functions.php');

session_start();

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

if ($_SESSION['toolbox_edit'] != 1) {
	header(($_SESSION['toolbox_view'] == 1) ? 'toolbox.php' : 'patients.php');
}

$document_data = array(
	'title'	=> ''
);

$document_id = (isset($_GET['id'])) ? (int) $_GET['id'] : 0;
$action = (isset($_GET['action'])) ? trim($_GET['action']) : '';

$success = true;
$message = '';

//email data
$action_text = '';
$action_doc = '';
if ($document_id > 0 && $action == 'delete') {
	//get document
	$data = array(
		'command'		=> 'toolbox_get_document',
		'agent' 		=> $_SESSION['agent'],
		'access_code'	=> $_SESSION['access_code'],
		'document_id'	=> $document_id
	);
	$toolbox_document = api_command($data);

	$action_text = 'deleted';
	$action_doc = $toolbox_document->document->title;
}

//actions
if ($action == 'save') {
	$document_data = array(
		'title'	=> (isset($_POST['title'])) ? trim($_POST['title']) : ''
	);

	//upload document
	$valid_upload = false;
	if (isset($_FILES['file'])) {
		if (count($_FILES['file']) > 0 && ($_FILES['file']['type'] == 'application/msword' || $_FILES['file']['type'] == 'application/vnd.openxmlformats-officedocument.wordprocessingml.document')) {
			$document_data['file_name'] = $_FILES['file']['name'];
			$document_data['file_contents'] = base64_encode(file_get_contents($_FILES['file']['tmp_name']));
			$valid_upload = true;
		} elseif (count($_FILES['file']) > 0 && $_FILES['file']['tmp_name'] != '' && $_FILES['file']['type'] != 'application/msword' && $_FILES['file']['type'] != 'application/vnd.openxmlformats-officedocument.wordprocessingml.document') {
			//invalid form
			$success = false;
			$message = 'Error: Please upload a MS Word file.<br/><br/>';
		}
	}

	//validate
	if ($document_data['title'] != '') {
		//valid file for inserts?
		if ($document_id == 0 && !$valid_upload) {
			//invalid form
			$success = false;
			$message = 'Error: Please upload a MS Word file.<br/><br/>';
		}

		if ($success) {
			//save document
			$data = array(
				'command'		=> 'toolbox_update_document',
				'agent' 		=> $_SESSION['agent'],
				'access_code'	=> $_SESSION['access_code'],
				'document_id'	=> $document_id,
				'document_data'	=> $document_data
			);

			$result = api_command($data);

			$action_text = 'updated';
			if ($document_id == 0) {
				$document_id = $result->document_id;
				$action_text = 'added';
			}

			$message = 'The ToolBox document was saved successfully.<br/><br/>';
		}
	} else {
		//invalid form
		$success = false;
		$message = 'Error: Please fill out at least the title field.<br/><br/>';
	}
} elseif ($action == 'delete') {
	//save document
	$data = array(
		'command'		=> 'toolbox_remove_document',
		'agent' 		=> $_SESSION['agent'],
		'access_code'	=> $_SESSION['access_code'],
		'document_id'	=> $document_id
	);

	$result = api_command($data);

	$message = 'The ToolBox document was successfully deleted.<br/><br/>';
}

if ($document_id > 0 && $action != 'delete') {
	//get document
	$data = array(
		'command'		=> 'toolbox_get_document',
		'agent' 		=> $_SESSION['agent'],
		'access_code'	=> $_SESSION['access_code'],
		'document_id'	=> $document_id
	);

	$toolbox_document = api_command($data);

	$document_data['title'] = (isset($toolbox_document->document->title)) ? $toolbox_document->document->title : $document_data['title'];
	$document_data['file'] = (isset($toolbox_document->document->file)) ? $toolbox_document->document->file : $document_data['file'];

	$action_doc = $document_data['title'];
}

//send email
if ($action != '' && $success) {
	require_once('../phpmailer/class.phpmailer.php');
	$mail = new PHPMailer(); // defaults to using php "mail()"
	$mail->CharSet = 'UTF-8';
	$mail->Encoding = "base64";
	$mail->isHTML(true);

	$mail->SetFrom('DoNotReply@prescriptionhope.com', 'Prescription Hope');
	$mail->AddReplyTo("DoNotReply@prescriptionhope.com","Prescription Hope");
	$mail->AddAddress('bryan@prescriptionhope.com');
	//$mail->AddAddress('georgep@wowbrands.com');
	$mail->Subject = 'ToolBox: ' . $_SESSION['agent_first_name'] . ' ' . $_SESSION['agent_last_name'] . ' ' . $action_text . ' ' . $action_doc;
	$mail->Body .= "<p style='font-family: Arial, sans-serif; font-size: 12px;'>" . 'ToolBox: ' . $_SESSION['agent_first_name'] . ' ' . $_SESSION['agent_last_name'] . ' ' . $action_text . ' ' . $action_doc . "</p>";

	$mail->Send();
}

?>

<?php include('_header.php'); ?>

<div class="">
	<h2>ToolBox - Edit Document</h2>
	<br/>

	<div id="fmMsg" class="<?=(($success) ? 'success' : 'error')?>"><?=$message?></div>

	<?php if ($action != 'delete') { ?>
		<form method='post' enctype="multipart/form-data" action='toolbox_edit_document.php?id=<?=$document_id?>&action=save'>
			<label for="title" class="label-long">Title</label>
			<input type="text" name="title" id="title" value="<?=$document_data['title']?>" class="">
			<br/><br/>

			<label for="file" class="label-long">File</label>
			<input type="file" name="file" id="file" value="" class="">
			<?php if (isset($document_data['file']) && $document_data['file'] != '') {
				$file_name = str_replace(sprintf('-%06d', $document_id), '', $document_data['file']);
				?>
				<br/>
				<label class="label-long">&nbsp;</label>
				<a href="toolbox_download_document.php?id=<?=$document_id?>"><?=$file_name?></a>
			<?php } ?>
			<br/><br/>

			<input type="submit" name="file_submit" id="btSubmit" value="Submit">
			<input type="button" name="file_delete" id="btDelete" value="Delete" onclick="if(window.confirm('Are you sure you want to delete this item?')) {window.location='toolbox_edit_document.php?id=<?=$document_id?>&action=delete';}">
		</div>
		</form>
	<?php } ?>
</div>

<?php include('_footer.php'); ?>
