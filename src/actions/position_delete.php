<?php
$id = filter_var($_POST['position_id'] ?? '', FILTER_VALIDATE_INT);
if ($id === false || $id < 1) {
    throw new HttpException(400, 'Invalid position ID.');
}
$count = (int) db_query('SELECT COUNT(*) FROM application WHERE position_id = :id', [':id' => $id])->fetchColumn();
if ($count > 0) {
    flash_add('error', 'This position has applications and cannot be deleted. Change its status to Closed instead.');
    redirect(return_url('positions', ['q', 'status', 'department', 'p']));
}
$stmt = db_query('DELETE FROM position WHERE position_id = :id', [':id' => $id]);
if ($stmt->rowCount() === 0) {
    throw new HttpException(404, 'Position not found.');
}
flash_add('success', 'Position deleted successfully.');
redirect(return_url('positions', ['q', 'status', 'department', 'p']));
