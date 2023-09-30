<?php

require_once('includes/functions.php');

session_start();

//check login
$agent_logged = is_agent_logged_in();
if (!$agent_logged) {
	header('Location: login.php');
}

if ($_SESSION['toolbox_view'] != 1 && $_SESSION['toolbox_edit'] != 1) {
	header('patients.php');
}

//get agent's toolbox
$data = array(
	'command'		=> 'toolbox_all_documents',
	'agent' 		=> $_SESSION['agent'],
	'access_code'	=> $_SESSION['access_code']
);

$toolbox_documents = api_command($data);

?>

<?php include('_header.php'); ?>

<div>
	<div class="">
		<h2>ToolBox</h2>
		<br/>

		<?=nl2br($_SESSION['toolbox_note'])?>

		<?php if ($_SESSION['toolbox_edit'] == 1) { ?>
			<br/><br/>
			To edit a toolbox item, select the "Update" link of the item you wish to update.
			<br/><br/>
		<?php } ?>
	</div><br/>

	<?php if ($_SESSION['toolbox_edit'] == 1) { ?>
		<a href="toolbox_edit_document.php?id=0" class="button">Add Item</a><br/><br/>
	<?php } ?>

	<?php if ($toolbox_documents->success == 1 && count($toolbox_documents->documents) > 0) { ?>
		<table class="no-border">
			<tbody>
			<?php foreach ($toolbox_documents->documents as $document) {
				$file_name = str_replace(sprintf('-%06d', $document->id), '', $document->file);
				?>
				<tr>
					<td><a href="toolbox_download_document.php?id=<?=$document->id?>"><?=$document->title?></a></td>
					<?php if ($_SESSION['toolbox_edit'] == 1) { ?>
						<td><a href="toolbox_edit_document.php?id=<?=$document->id?>" class="button">Update</a></td>
					<?php } ?>
				</tr>
			<?php } ?>
			</tbody>
		</table>
	<?php } else { ?>
		<br/>
		There are no items to show.
	<?php } ?>
</div>

<?php include('_footer.php'); ?>
