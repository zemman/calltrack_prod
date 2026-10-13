<?php
include 'views/layout/header.php';
?>

<h2>Редактировать обращение</h2>
<form method="post">
    <input type="hidden" name="action" value="ticket_save">
    <input type="hidden" name="id" value="<?php echo (int)$ticket['id']; ?>">
    
    <label>Тип<br>
        <select name="type">
            <option value="call" <?php echo $ticket['type'] === 'call' ? 'selected' : ''; ?>>Звонок</option>
            <option value="email" <?php echo $ticket['type'] === 'email' ? 'selected' : ''; ?>>Email</option>
        </select>
    </label><br><br>
    
    <label>Подтема1<br>
        <select name="topic1_id" id="topic1">
            <option value="">—</option>
            <?php foreach ($topic1 as $t) { 
                $sel = (int)$ticket['topic1_id'] === (int)$t['id'] ? 'selected' : '';
                echo '<option value="'.$t['id'].'" '.$sel.'>'.Utils::h($t['name']).'</option>'; 
            } ?>
        </select>
    </label><br><br>
    
    <label>Подтема2<br>
        <select name="topic2_id" id="topic2" <?php echo !$ticket['topic1_id'] ? 'disabled' : ''; ?>>
            <option value="">—</option>
            <?php if ($ticket['topic1_id']): ?>
                <?php 
                $topic2_for_topic1 = Database::fetchAll("SELECT * FROM topic2 WHERE topic1_id = ? ORDER BY name", array($ticket['topic1_id']));
                foreach ($topic2_for_topic1 as $t2) { 
                    $sel = (int)$ticket['topic2_id'] === (int)$t2['id'] ? 'selected' : '';
                    echo '<option value="'.$t2['id'].'" '.$sel.'>'.Utils::h($t2['name']).'</option>'; 
                } 
                ?>
            <?php endif; ?>
        </select>
    </label><br><br>
    
    <label>Магазин<br>
        <select name="store_id" id="store_id">
            <option value="">—</option>
            <?php foreach ($stores as $s) { 
                $sel = (int)$ticket['store_id'] === (int)$s['id'] ? 'selected' : '';
                echo '<option value="'.$s['id'].'" '.$sel.'>'.Utils::h($s['name']).'</option>'; 
            } ?>
        </select>
    </label><br><br>
    
    
    <label>Дата обращения<br>
        <input type="datetime-local" name="occurred_at" value="<?php echo date('Y-m-d\TH:i', strtotime($ticket['occurred_at'])); ?>">
    </label><br><br>
    
    <label>Описание<br>
        <textarea name="description" rows="5" cols="60"><?php echo Utils::h($ticket['description']); ?></textarea>
    </label><br><br>
    
    <label>Статус<br>
        <select name="status">
            <option value="open" <?php echo $ticket['status'] === 'open' ? 'selected' : ''; ?>>Открыт</option>
            <option value="in_progress" <?php echo $ticket['status'] === 'in_progress' ? 'selected' : ''; ?>>В работе</option>
            <option value="closed" <?php echo $ticket['status'] === 'closed' ? 'selected' : ''; ?>>Закрыт</option>
        </select>
    </label><br><br>
    
    <label><input type="checkbox" name="justified" <?php echo $ticket['justified'] ? 'checked' : ''; ?>> Обосновано</label><br><br>
    
    <label>Комментарий обоснования<br>
        <textarea name="justify_comment" rows="3" cols="60"><?php echo Utils::h($ticket['justify_comment']); ?></textarea>
    </label><br><br>
    
    <div style="margin-top: 20px; padding: 10px; background: #f3f4f6; border-radius: 6px;">
        <strong>Информация о создании:</strong><br>
        Создано: <?php echo Utils::h($ticket['created_at']); ?><br>
        Обновлено: <?php echo Utils::h($ticket['updated_at']); ?>
    </div><br>
    
    <button class="btn" type="submit">Сохранить</button>
    <a class="btn btn-secondary" href="index.php?page=tickets">Отмена</a>
</form>

<script>
document.getElementById('topic1').addEventListener('change', function() {
    var t1 = this.value;
    var t2 = document.getElementById('topic2');
    t2.innerHTML = '<option value="">—</option>';
    if (!t1) { t2.disabled = true; return; }
    var xhr = new XMLHttpRequest();
    xhr.open('GET', 'topic2.php?topic1_id=' + encodeURIComponent(t1));
    xhr.onload = function() {
        try {
            var data = JSON.parse(xhr.responseText);
            for (var i=0; i<data.length; i++) {
                var opt = document.createElement('option');
                opt.value = data[i].id; opt.textContent = data[i].name;
                t2.appendChild(opt);
            }
            t2.disabled = false;
        } catch(e) { t2.disabled = true; }
    };
    xhr.send();
});

$(function(){
    var $store = $('#store_id');
    if ($store.length && $.fn.select2) {
        $store.select2({
            width: 'resolve',
            placeholder: 'Выберите магазин',
            allowClear: true,
            language: {
                errorLoading: function () { return 'Результаты не могут быть загружены.'; },
                inputTooLong: function (args) { var overChars = args.input.length - args.maximum; return 'Пожалуйста, удалите ' + overChars + ' символ' + (overChars>1?'а':''); },
                inputTooShort: function (args) { var remainingChars = args.minimum - args.input.length; return 'Пожалуйста, введите ещё ' + remainingChars + ' символ' + (remainingChars>1?'а':''); },
                loadingMore: function () { return 'Загрузка дополнительных результатов…'; },
                maximumSelected: function (args) { return 'Вы можете выбрать не более ' + args.maximum + ' элемент' + (args.maximum>1?'ов':'а'); },
                noResults: function () { return 'Совпадений не найдено'; },
                searching: function () { return 'Поиск…'; }
            }
        });
    }
});
</script>

<?php include 'views/layout/footer.php'; ?>