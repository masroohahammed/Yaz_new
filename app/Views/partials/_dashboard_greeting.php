<?php
/** Dashboard page header subtitle — Hi, {name} */
$uName = trim((string) ($currentUser['name'] ?? session()->get('user_name') ?? ''));
if ($uName === '') {
    $uName = 'User';
}
?>
<div class="small text-muted dashboard-greeting">Hi, <strong><?= esc($uName) ?></strong><?= !empty($showDate) ? ' · ' . date('l, d F Y') : '' ?></div>
