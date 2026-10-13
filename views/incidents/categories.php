<?php
include 'views/layout/header.php';
?>

<h2>Категории инцидентов</h2>

<div class="card" style="margin-bottom: 20px;">
    <h3>Добавить категорию</h3>
    <form method="post" style="display: flex; gap: 10px; align-items: end;">
        <input type="hidden" name="action" value="incident_category_save">
        <label style="flex: 1;">
            Название категории
            <input type="text" name="name" required style="width: 100%;">
        </label>
        <button class="btn" type="submit">Добавить</button>
    </form>
</div>

<div class="table-wrap">
<table>
    <thead>
    <tr>
        <th>ID</th><th>Название</th><th>Создана</th><th>Обновлена</th><th>Действия</th>
    </tr>
    </thead>
    <tbody>
    <?php foreach ($categories as $c): ?>
        <tr>
            <td><?php echo (int)$c['id']; ?></td>
            <td>
                <form method="post" class="inline" style="display: inline;">
                    <input type="hidden" name="action" value="incident_category_save">
                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                    <input type="text" name="name" value="<?php echo Utils::h($c['name']); ?>" style="width: 200px;">
                    <button class="btn" type="submit" style="padding: 4px 8px; font-size: 12px;">Сохранить</button>
                </form>
            </td>
            <td><?php echo Utils::h($c['created_at']); ?></td>
            <td><?php echo Utils::h($c['updated_at']); ?></td>
            <td>
                <form method="post" class="inline" style="display: inline;" onsubmit="return confirm('Удалить категорию?')">
                    <input type="hidden" name="action" value="incident_category_delete">
                    <input type="hidden" name="id" value="<?php echo (int)$c['id']; ?>">
                    <button class="btn btn-secondary" type="submit" style="padding: 4px 8px; font-size: 12px;">Удалить</button>
                </form>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>

<div style="margin-top: 20px;">
    <a class="btn" href="index.php?page=incidents">← Назад к инцидентам</a>
</div>

<?php include 'views/layout/footer.php'; ?>
