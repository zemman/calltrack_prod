<?php
include 'views/layout/header.php';
?>

<h2>Отчёты / Экспорт</h2>

<div class="card" style="margin-bottom: 20px;">
    <h3>Экспорт обращений</h3>
    <p>Экспорт всех обращений в формате CSV с возможностью фильтрации по датам и другим параметрам.</p>
    <a class="btn" href="index.php?page=export_csv&type=tickets">Экспорт обращений CSV</a>
</div>

<div class="card" style="margin-bottom: 20px;">
    <h3>Экспорт инцидентов</h3>
    <p>Экспорт всех инцидентов в формате CSV с возможностью фильтрации по датам, категориям и статусам.</p>
    <a class="btn" href="index.php?page=export_csv&type=incidents">Экспорт инцидентов CSV</a>
</div>

<div class="card">
    <h3>Статистика</h3>
    <p>Общая статистика по системе:</p>
    <ul>
        <li>Всего обращений: <?php echo Database::fetchValue("SELECT COUNT(*) FROM tickets"); ?></li>
        <li>Открытых обращений: <?php echo Database::fetchValue("SELECT COUNT(*) FROM tickets WHERE status = 'open'"); ?></li>
        <li>Всего инцидентов: <?php echo Database::fetchValue("SELECT COUNT(*) FROM incidents"); ?></li>
        <li>Открытых инцидентов: <?php echo Database::fetchValue("SELECT COUNT(*) FROM incidents WHERE status = 'open'"); ?></li>
    </ul>
</div>

<?php include 'views/layout/footer.php'; ?>