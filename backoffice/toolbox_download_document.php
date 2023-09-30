<?php

require_once('includes/functions.php');

session_start();

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

if ($_SESSION['toolbox_view'] == 1 || $_SESSION['toolbox_edit'] == 1) {
	$document_id = (isset($_GET['id'])) ? (int) $_GET['id'] : 0;

	if ($document_id > 0) {
		//get document for download
		$data = array(
			'command'		=> 'toolbox_download_document',
			'agent' 		=> $_SESSION['agent'],
			'access_code'	=> $_SESSION['access_code'],
			'document_id'	=> $document_id
		);

		$document_file = api_command($data);

		if (isset($document_file->success) && $document_file->success == 1 && isset($document_file->document->file_contents) && $document_file->document->file_contents != '') {
			$file_name = str_replace(sprintf('-%06d', $document_id), '', $document_file->document->file);
			header('Content-Disposition: filename="' . $file_name . '"');
			header("Content-type: application/msword");
			echo base64_decode($document_file->document->file_contents);
		}
	}
}