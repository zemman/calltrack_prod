<h2>Справочник Тема2</h2>
<form method="post" class="inline">
    <label>Название <input name="name" required></label>
    <label>Тема1
        <select name="topic1_id" required>
            <option value="">—</option>
            <?php foreach ($topic1 as $t) { echo '<option value="'.$t['id'].'">'.Utils::h($t['name']).'</option>'; } ?>
        </select>
    </label>
    <button class="btn" type="submit">Добавить</button>
    <a class="btn btn-secondary" href="index.php?page=admin_topic2">Обновить</a>
    <a class="btn btn-secondary" href="index.php?page=tickets">Назад</a>
 </form>
<div class="table-wrap">
<table>
    <thead><tr><th>ID</th><th>Тема1</th><th>Название</th><th></th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
        <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo Utils::h($r['topic1_name']); ?></td>
            <td><?php echo Utils::h($r['name']); ?></td>
            <td>
                <form method="post" class="inline" style="margin-right:6px">
                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                    <input name="name" value="<?php echo Utils::h($r['name']); ?>">
                    <select name="topic1_id">
                        <?php foreach ($topic1 as $t) { $sel = ((int)$t['id']===(int)$r['topic1_id'])? 'selected':''; echo '<option value="'.$t['id'].'" '.$sel.'>'.Utils::h($t['name']).'</option>'; } ?>
                    </select>
                    <button class="btn btn-secondary" type="submit">Сохранить</button>
                </form>
                <a href="index.php?page=admin_topic2&delete=<?php echo (int)$r['id']; ?>" onclick="return confirm('Удалить?')">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
