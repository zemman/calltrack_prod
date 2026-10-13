<h2>Создать звонок</h2>
<form method="post">
    <input type="hidden" name="action" value="ticket_save">
    <input type="hidden" name="status" value="closed">
    <label>Подтема1<br>
        <select name="topic1_id" id="topic1">
            <option value="">—</option>
            <?php foreach ($topic1 as $t) { echo '<option value="'.$t['id'].'">'.Utils::h($t['name']).'</option>'; } ?>
        </select>
    </label><br>
    <label>Подтема2<br>
        <select name="topic2_id" id="topic2" disabled>
            <option value="">—</option>
        </select>
    </label><br>
    <label>Магазин<br>
        <select name="store_id" id="store_id">
            <option value="">—</option>
            <?php foreach ($stores as $s) { echo '<option value="'.$s['id'].'">'.Utils::h($s['name']).'</option>'; } ?>
        </select>
    </label><br>
    <label>Дата обращения<br>
        <input type="datetime-local" name="occurred_at" value="<?php echo date('Y-m-d\TH:i'); ?>">
    </label><br>
    <label>Описание<br>
        <textarea name="description" rows="5" cols="60"></textarea>
    </label><br>
    <label><input type="checkbox" name="justified"> Обосновано</label><br>
    <button class="btn" type="submit">Сохранить</button>
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
