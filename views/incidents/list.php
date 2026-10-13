<?php
include 'views/layout/header.php';
?>

<h2>Список инцидентов</h2>
<form method="get" class="filters">
    <input type="hidden" name="page" value="incidents">
    <label>С:
        <input type="datetime-local" name="from" value="<?php echo isset($_GET['from'])?Utils::h($_GET['from']):''; ?>">
    </label>
    <label>По:
        <input type="datetime-local" name="to" value="<?php echo isset($_GET['to'])?Utils::h($_GET['to']):''; ?>">
    </label>
    <label>Категория
        <select name="category_id">
            <option value="">—</option>
            <?php foreach ($incident_categories as $c) { 
                $sel = (isset($_GET['category_id']) && (int)$_GET['category_id']===(int)$c['id'])? 'selected':'';
                echo '<option value="'.$c['id'].'" '.$sel.'>'.Utils::h($c['name']).'</option>'; 
            } ?>
        </select>
    </label>
    <label>Статус
        <select name="status">
            <option value="">—</option>
            <option value="open" <?php echo (isset($_GET['status']) && $_GET['status']==='open')?'selected':''; ?>>Открыт</option>
            <option value="closed" <?php echo (isset($_GET['status']) && $_GET['status']==='closed')?'selected':''; ?>>Закрыт</option>
        </select>
    </label>
    <label>Поиск
        <input name="q" value="<?php echo isset($_GET['q'])?Utils::h($_GET['q']):''; ?>">
    </label>
    <button class="btn" type="submit">Фильтр</button>
    <a class="btn btn-secondary" href="index.php?page=export_csv&type=incidents&<?php echo http_build_query(array_merge($_GET, array('page'=>null, 'type'=>null))); ?>">Экспорт CSV</a>
    <a class="btn" href="index.php?page=incident_new">Создать инцидент</a>
</form>

<div class="table-wrap">
<table>
    <thead>
    <tr>
        <th>ID</th><th>Заголовок</th><th>Статус</th><th>Категория</th><th>Дата начала</th><th>Дата завершения</th><th>Обновлено</th><th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo Utils::h($r['title']); ?></td>
            <td>
                <span style="color: <?php echo $r['status'] === 'open' ? '#ef4444' : '#10b981'; ?>">
                    <?php echo $r['status'] === 'open' ? 'Открыт' : 'Закрыт'; ?>
                </span>
            </td>
            <td><?php echo Utils::h($r['category_name'] ?: ''); ?></td>
            <td><?php echo Utils::h($r['started_at']); ?></td>
            <td><?php echo Utils::h($r['finished_at'] ?: ''); ?></td>
            <td><?php echo Utils::h($r['updated_at']); ?></td>
            <td><a class="btn" style="color:#fff" href="index.php?page=incident_edit&id=<?php echo (int)$r['id']; ?>">Открыть</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<?php include 'views/layout/footer.php'; ?>
