<?php
if (!empty($GLOBALS['CALLTRACK_LAYOUT_OPEN'])) {
    return;
}
$GLOBALS['CALLTRACK_LAYOUT_OPEN'] = true;
$user = isset($_SESSION['user']) ? $_SESSION['user'] : null;
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>CallTrack</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Open+Sans:wght@400;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet">
    <link href="assets/style.css" rel="stylesheet">
    <style>
        body{font-family:'Open Sans',Arial,Helvetica,sans-serif;margin:0;background:#f8fafc;color:#111827}
        header{background:#d50000;color:#fff;padding:14px 18px}
        nav{background:#111827;color:#fff;padding:10px 18px}
        main{padding:20px;max-width:1100px;margin:0 auto}
        a{color:#2563eb;text-decoration:none}
        nav a{color:#fff;margin-right:14px}
        table{border-collapse:separate;border-spacing:0;width:100%;background:#fff;border:1px solid #e5e7eb;border-radius:8px;overflow:hidden}
        th,td{border-bottom:1px solid #e5e7eb;padding:10px 12px;text-align:left}
        th{background:#f3f4f6;font-weight:600}
        tr:nth-child(even){background:#fafafa}
        .card{background:#fff;border:1px solid #e5e7eb;border-radius:8px;padding:16px}
        .flash{padding:10px 12px;margin:12px 0;border-radius:6px}
        .success{background:#ecfdf5;border:1px solid #10b981}
        .error{background:#fef2f2;border:1px solid #ef4444}
        .btn{display:inline-block;background:#2563eb;color:#fff;padding:8px 12px;border:none;border-radius:6px;cursor:pointer;font-weight:600}
        .btn:hover{background:#1d4ed8}
        .btn-secondary{background:#6b7280}
        .btn-secondary:hover{background:#4b5563}
        .btn-link{background:transparent;color:#2563eb;padding:0}
        input,select,textarea{padding:8px 10px;margin:6px 0;border:1px solid #e5e7eb;border-radius:6px;background:#fff;width:auto}
        form.inline{display:inline}
        .select2-container--default .select2-selection--single{height:38px;border:1px solid #e5e7eb;border-radius:6px}
        .select2-container--default .select2-selection--single .select2-selection__rendered{line-height:36px;padding-left:10px}
        .select2-container--default .select2-selection--single .select2-selection__arrow{height:36px}
    </style>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
</head>
<body>
<header class="app-header">

    <svg class="app-logo" width="130" height="40" fill="none" xmlns="http://www.w3.org/2000/svg">
      <path fill-rule="evenodd" clip-rule="evenodd" d="M130 39.364h-7.724v-9.006h-7.286v9.006h-7.759V15.45h7.759v8.77h7.286v-8.77H130v23.915ZM30.662 10.423a1.75 1.75 0 0 0 0 2.462 1.75 1.75 0 0 0 2.462 0 1.751 1.751 0 0 0 0-2.462 1.75 1.75 0 0 0-2.462 0Zm6.24 1.417s-1.012 7.555-3.98 12.345a4.466 4.466 0 0 1-.377.49c-3 4.311-8.143 4.873-10.35 4.873l3.542 9.85h-2.193L19.7 28.671c-.034-.168 0-.27.101-.303 1.51-.59 1.87-.798 2.912-1.399l.09-.052h.019c3.17-2.06 4.876-5.473 5.58-7.15.034-.102.034-.203-.067-.27l-.015-.005a18.35 18.35 0 0 1-.626 1.084c-2.766 4.183-6.982 7.151-13.83 7.151H4.386c-.236 0-.236-.236-.135-.337l2.496-2.53c-3.474 0-5.498-2.058-6.24-4.351-.068-.135-.135-.405.135-.405h10.625c1.113 0 1.89-.337 2.53-.91l8.467-7.05c-3.171 2.732-7.489 6.948-11.402 12.345a.178.178 0 0 0-.034.13.24.24 0 0 1 .034-.096c3.913-5.397 8.23-9.614 11.402-12.346C25.028 9.782 26.917 8.5 26.917 8.5c1.316-.843 2.766-1.282 4.42-1.282 2.78 0 4.663 1.665 5.217 2.712l.01-.013h3.475c.236 0 .303.304.101.405l-3.238 1.484-1.147-.506c-.096-.024-.14-.116-.11-.216-.061.112.003.223.11.25l1.147.506Zm-1.585 11.333c-1.62 3.373-3.171 5.06-6.747 6.78l3.34 9.411h9.377c-1.923-5.768-4.014-11.198-5.97-16.19ZM9.478 39.364H0c1.152-3.654 2.305-6.86 3.363-9.802l.078-.216h9.242L9.478 39.364Zm1.552-20.947c.675 0 1.282-.101 1.788-.54l4.79-3.946c.978-2.732 2.057-5.532 3.137-7.86.0573 1.249 1.08 2.497 1.619 3.846 2.63-2.159 4.25-3.44 6.409-4.048A457.76 457.76 0 0 0 26.04 0H15.415c-.91 1.889-4.284 8.905-8.23 18.417h3.845Zm60.885 20.947-.506-2.63c-1.687 1.787-3.778 3.136-7.556 3.102-3.204 0-5.397-1.079-6.577-3.238-1.755 1.856-3.947 3.239-7.725 3.239-5.228 0-7.724-2.834-7.724-8.602V15.45h7.724v14.774c0 2.226 1.113 3.474 3.103 3.474 1.282 0 2.834-.843 3.475-1.652V15.449h7.724v14.774c0 2.226 1.18 3.474 3.34 3.474 1.18 0 2.597-.843 3.238-1.652V15.449h7.758v23.915h-6.274Zm20.306-5.666c1.889 0 3.036-.844 3.744-1.653V21.487c-1.045-.236-1.788-.337-3.238-.337-2.496 0-3.744 2.293-3.744 6.88 0 3.711 1.147 5.667 3.238 5.667ZM89.86 39.87h-.236c-5.734-.033-8.703-3.541-8.703-11.536 0-8.635 4.216-13.357 11.941-13.357 5.43 0 8.332.674 10.861 1.484v22.903h-6.307l-.405-2.63c-1.316 1.922-3.71 3.102-7.151 3.136Zm-63.077-9.276c-.608.169-1.316.304-2.058.405l3.002 8.365h2.193l-3.137-8.77Zm5.194-20.171a.836.836 0 0 0 0 1.147.836.836 0 0 0 1.147 0 .836.836 0 0 0 0-1.147.836.836 0 0 0-1.147 0Z" fill="#fff"></path>
    </svg>
    
    <strong>CallTrack v.0.3</strong>
    <?php if ($user) { echo '<span class="app-user">Вы вошли как '.Utils::h($user['name']).' ('.Utils::h($user['role']).')</span>'; } ?>
</header>
<nav class="app-nav">
    <?php if ($user): ?>
        <a href="index.php?page=tickets">Список обращений</a>
        <a href="index.php?page=ticket_new_call">Создать звонок</a>
        <a href="index.php?page=incidents">Инциденты</a>
        <a href="index.php?page=incident_new">Создать инцидент</a>
        <a href="index.php?page=reports">Отчёты / Экспорт</a>
        <?php if ($user && $user['role']==='admin'): ?>
            <a href="index.php?page=admin_users">Пользователи</a>
            <a href="index.php?page=admin_stores">Магазины</a>
            <a href="index.php?page=admin_topic1">Темы1</a>
            <a href="index.php?page=admin_topic2">Темы2</a>
            <a href="index.php?page=incident_categories">Категории инцидентов</a>
            <a href="index.php?page=admin_settings">SMTP-настройки</a>
        <?php endif; ?>
        <a href="index.php?page=logout">Выйти</a>
    <?php endif; ?>
</nav>
<main>
    <?php if ($m = Utils::flash('flash_success')): ?><div class="flash success"><?php echo Utils::h($m); ?></div><?php endif; ?>
    <?php if ($m = Utils::flash('flash_error')): ?><div class="flash error"><?php echo Utils::h($m); ?></div><?php endif; ?>
