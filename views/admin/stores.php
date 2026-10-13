<h2>Справочник магазинов</h2>
<form method="post" class="inline">
    <label>Название <input name="name" required></label>
    <button class="btn" type="submit">Добавить</button>
    <a class="btn btn-secondary" href="index.php?page=admin_stores">Обновить</a>
    <a class="btn btn-secondary" href="index.php?page=tickets">Назад</a>
 </form>
<div class="table-wrap">
<table>
    <thead><tr><th>ID</th><th>Название</th><th></th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
        <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo Utils::h($r['name']); ?></td>
            <td>
                <form method="post" class="inline" style="margin-right:6px">
                    <input type="hidden" name="id" value="<?php echo (int)$r['id']; ?>">
                    <input name="name" value="<?php echo Utils::h($r['name']); ?>">
                    <button class="btn btn-secondary" type="submit">Сохранить</button>
                </form>
                <a href="index.php?page=admin_stores&delete=<?php echo (int)$r['id']; ?>" onclick="return confirm('Удалить?')">Удалить</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
