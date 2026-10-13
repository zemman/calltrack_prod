<?php
include 'views/layout/header.php';
?>

<h2>Создать инцидент</h2>
<form method="post">
    <input type="hidden" name="action" value="incident_save">

    <div class="form-row">
    <label>Заголовок *
        <input type="text" name="title" required>
    </label>
    </div>

    <div class="form-row">
    <label>Описание *
        <textarea name="description" rows="5" required></textarea>
    </label>
    </div>

    <div class="form-grid">
    <label>Категория *
        <select name="category_id" required>
            <option value="">—</option>
            <?php foreach ($incident_categories as $c) { 
                echo '<option value="'.$c['id'].'">'.Utils::h($c['name']).'</option>'; 
            } ?>
        </select>
    </label>

    <label>Статус *
        <select name="status" required>
            <option value="open">Открыт</option>
            <option value="closed">Закрыт</option>
        </select>
        <span class="status-note">Статус будет установлен в "Закрыт" автоматически при указании даты завершения.</span>
    </label>

    <label>Дата начала *
        <input type="datetime-local" name="started_at" value="<?php echo date('Y-m-d\TH:i'); ?>" required>
    </label>

    <label>Дата завершения
        <input type="datetime-local" name="finished_at">
    </label>
    </div>

    <div class="form-row">
    <label>Номер INC в SO
        <input type="text" name="inc_number_so">
    </label>
    </div>

    <div class="form-row">
    <label>Потенциальное влияние
        <textarea name="potential_impact" rows="3"></textarea>
    </label>
    </div>

    <div class="form-row">
    <label>Оповещение в бот
        <textarea name="bot_notification" rows="3"></textarea>
    </label>
    </div>

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
