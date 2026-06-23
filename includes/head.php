<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= isset($page_title) ? sanitize($page_title) . ' | SportZone' : 'SportZone - Sports Equipment & Apparel' ?></title>
    <meta name="csrf-token" content="<?= csrf_token() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>assets/css/style.css">
    <script>window.SZ_BASE = '<?= BASE_URL ?>';</script>
</head>
<body<?= isset($body_page) ? ' data-page="' . sanitize($body_page) . '"' : '' ?>>
