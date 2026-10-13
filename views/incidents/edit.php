<?php
include 'views/layout/header.php';
?>

<h2>Редактировать инцидент</h2>
<form method="post">
    <input type="hidden" name="action" value="incident_save">
    <input type="hidden" name="id" value="<?php echo (int)$incident['id']; ?>">
    
    <div class="form-row">
    <label>Заголовок *
        <input type="text" name="title" value="<?php echo Utils::h($incident['title']); ?>" required>
    </label>
    </div>

    <div class="form-row">
    <label>Описание *
        <textarea name="description" rows="5" required><?php echo Utils::h($incident['description']); ?></textarea>
    </label>
    </div>

    <div class="form-grid">
    <label>Статус *
        <select name="status" required>
            <option value="open" <?php echo $incident['status'] === 'open' ? 'selected' : ''; ?>>Открыт</option>
            <option value="closed" <?php echo $incident['status'] === 'closed' ? 'selected' : ''; ?>>Закрыт</option>
        </select>
        <span class="status-note">Статус будет установлен в "Закрыт" автоматически при указании даты завершения.</span>
    </label>

    <label>Дата начала *
        <input type="datetime-local" name="started_at" value="<?php echo date('Y-m-d\TH:i', strtotime($incident['started_at'])); ?>" required>
    </label>

    <label>Дата завершения
        <input type="datetime-local" name="finished_at" value="<?php echo $incident['finished_at'] ? date('Y-m-d\TH:i', strtotime($incident['finished_at'])) : ''; ?>">
    </label>

    <label>Категория *
        <select name="category_id" required>
            <option value="">—</option>
            <?php foreach ($incident_categories as $c) { 
                $sel = (int)$incident['category_id'] === (int)$c['id'] ? 'selected' : '';
                echo '<option value="'.$c['id'].'" '.$sel.'>'.Utils::h($c['name']).'</option>'; 
            } ?>
        </select>
    </label>
    </div>

    <div class="form-row">
    <label>Номер INC в SO
        <input type="text" name="inc_number_so" value="<?php echo Utils::h($incident['inc_number_so']); ?>">
    </label>
    </div>

    <div class="form-row">
    <label>Потенциальное влияние
        <textarea name="potential_impact" rows="3"><?php echo Utils::h($incident['potential_impact']); ?></textarea>
    </label>
    </div>

    <div class="form-row">
    <label>Оповещение в бот
        <textarea name="bot_notification" rows="3"><?php echo Utils::h($incident['bot_notification']); ?></textarea>
    </label>
    </div>
    
    <div style="margin-top: 20px; padding: 10px; background: #f3f4f6; border-radius: 6px;">
        <strong>Информация о создании:</strong><br>
        Создан: <?php echo Utils::h($incident['created_at']); ?><br>
        Обновлен: <?php echo Utils::h($incident['updated_at']); ?>
    </div><br>
    
    <div class="form-actions">
        <button class="btn" type="submit">Сохранить</button>
        <a class="btn btn-secondary" href="index.php?page=incidents">Отмена</a>
    </div>
</form>

<script>
// Автоматическое заполнение даты завершения при выборе статуса "Закрыт"
document.querySelector('select[name="status"]').addEventListener('change', function() {
    const finishedAtInput = document.querySelector('input[name="finished_at"]');
    if (this.value === 'closed' && !finishedAtInput.value) {
        finishedAtInput.value = new Date().toISOString().slice(0, 16);
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>
