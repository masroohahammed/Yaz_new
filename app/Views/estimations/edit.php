<?php
// Edit view reuses create form with $est pre-populated and $items for line items
$est = $est ?? [];
$items = $items ?? [];
include __DIR__ . '/create.php';
