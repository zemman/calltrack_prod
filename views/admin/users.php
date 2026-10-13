<h2>Пользователи</h2>
<form method="post" class="inline">
    <label>Имя <input name="name" required></label>
    <label>Email <input name="email" type="email" required></label>
    <label>Пароль <input name="password" type="password" required></label>
    <label>Роль
        <select name="role">
            <option value="operator">operator</option>
            <option value="admin">admin</option>
        </select>
    </label>
    <button class="btn" type="submit">Добавить</button>
    <a class="btn btn-secondary" href="index.php?page=admin_users">Обновить</a>
    <a class="btn btn-secondary" href="index.php?page=tickets">Назад</a>
 </form>
<div class="table-wrap">
<table>
    <thead><tr><th>ID</th><th>Имя</th><th>Email</th><th>Роль</th><th></th></tr></thead>
    <tbody>
    <?php foreach($rows as $r): ?>
        <tr>
            <td><?php echo (int)$r['id']; ?></td>
            <td><?php echo Utils::h($r['name']); ?></td>
            <td><?php echo Utils::h($r['email']); ?></td>
            <td><?php echo Utils::h($r['role']); ?></td>
            <td><a href="index.php?page=admin_users&delete=<?php echo (int)$r['id']; ?>" onclick="return confirm('Удалить?')">Удалить</a></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
</div>
