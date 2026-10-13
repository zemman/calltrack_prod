<h2>Список обращений</h2>
<form method="get" class="filters">
    <input type="hidden" name="page" value="tickets">
    <label>С:
        <input type="datetime-local" name="from" value="<?php echo isset($_GET['from'])?Utils::h($_GET['from']):''; ?>">
    </label>
    <label>По:
        <input type="datetime-local" name="to" value="<?php echo isset($_GET['to'])?Utils::h($_GET['to']):''; ?>">
    </label>
    <label>Тема1
        <select name="topic1_id">
            <option value="">—</option>
            <?php foreach ($topic1 as $t) { $sel = (isset($_GET['topic1_id']) && (int)$_GET['topic1_id']===(int)$t['id'])? 'selected':''; echo '<option value="'.$t['id'].'" '.$sel.'>'.Utils::h($t['name']).'</option>'; } ?>
        </select>
    </label>
    <label>Магазин
        <select name="store_id">
            <option value="">—</option>
            <?php foreach ($stores as $s) { $sel = (isset($_GET['store_id']) && (int)$_GET['store_id']===(int)$s['id'])? 'selected':''; echo '<option value="'.$s['id'].'" '.$sel.'>'.Utils::h($s['name']).'</option>'; } ?>
        </select>
    </label>
    <label>Сотрудник
        <select name="employee_id">
            <option value="">—</option>
            <?php foreach ($users as $u) { $sel = (isset($_GET['employee_id']) && (int)$_GET['employee_id']===(int)$u['id'])? 'selected':''; echo '<option value="'.$u['id'].'" '.$sel.'>'.Utils::h($u['name']).'</option>'; } ?>
        </select>
    </label>
    <label>Статус
        <select name="status">
            <option value="">—</option>
            <?php foreach (array('open'=>'Открыт','in_progress'=>'В работе','closed'=>'Закрыт') as $k=>$v) { $sel = (isset($_GET['status']) && $_GET['status']===$k)? 'selected':''; echo '<option value="'.$k.'" '.$sel.'>'.Utils::h($v).'</option>'; } ?>
        </select>
    </label>
    <label>Обосновано
        <select name="justified">
            <option value="">—</option>
            <option value="1" <?php echo (isset($_GET['justified']) && $_GET['justified']==='1')?'selected':''; ?>>Да</option>
            <option value="0" <?php echo (isset($_GET['justified']) && $_GET['justified']==='0')?'selected':''; ?>>Нет</option>
        </select>
    </label>
    <label>Поиск
        <input name="q" value="<?php echo isset($_GET['q'])?Utils::h($_GET['q']):''; ?>">
    </label>
    <button class="btn" type="submit">Фильтр</button>
    <a class="btn btn-secondary" href="index.php?page=export_csv&<?php echo http_build_query(array_merge($_GET, array('page'=>null))); ?>">Экспорт CSV</a>
</form>

<div class="table-wrap">
<table>
    <thead>
    <tr>
        <th>ID</th><th>Тип</th><th>Тема1</th><th>Тема2</th><th>Магазин</th><th>Сотрудник</th><th>Дата</th><th>Обосновано</th><th>Статус</th><th>Обновлено</th><th></th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($rows as $r): ?>
        <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo Utils::h($r['type']); ?></td>
            <td><?php echo Utils::h(isset($r['topic1_name'])?$r['topic1_name']:''); ?></td>
            <td><?php echo Utils::h(isset($r['topic2_name'])?$r['topic2_name']:''); ?></td>
            <td><?php echo Utils::h(isset($r['store_name'])?$r['store_name']:''); ?></td>
            <td><?php echo Utils::h(isset($r['employee_name'])?$r['employee_name']:''); ?></td>
            <td><?php echo Utils::h($r['occurred_at']); ?></td>
            <td><?php echo $r['justified']? 'Да':'Нет'; ?></td>
            <td><?php echo Utils::h($r['status']); ?></td>
            <td><?php echo Utils::h($r['updated_at']); ?></td>
            <td><a class="btn" style="color:#fff" href="index.php?page=ticket_edit&id=<?php echo (int)$r['id']; ?>">Открыть</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
