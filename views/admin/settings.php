<h2>SMTP-настройки</h2>

<div class="card">
    <form method="post" class="settings-form">
        <input type="hidden" name="action" value="admin_settings_save">

        <label>SMTP Host
            <input type="text" name="smtp_host" value="<?php echo Utils::h(isset($settings['smtp_host']) ? $settings['smtp_host'] : ''); ?>" placeholder="smtp.yandex.ru">
        </label>

        <label>SMTP Port
            <input type="number" name="smtp_port" value="<?php echo Utils::h(isset($settings['smtp_port']) ? $settings['smtp_port'] : '587'); ?>" min="1" max="65535">
        </label>

        <label>SMTP Username
            <input type="email" name="smtp_username" value="<?php echo Utils::h(isset($settings['smtp_username']) ? $settings['smtp_username'] : ''); ?>" placeholder="sender@example.com">
        </label>

        <label>SMTP Password
            <input type="password" name="smtp_password" value="" autocomplete="new-password" placeholder="Оставьте пустым, чтобы не менять">
            <span class="help-text">Пароль приложения сохраняется в настройках и не показывается повторно в форме.</span>
        </label>

        <label>Encryption
            <select name="smtp_encryption">
                <?php $enc = isset($settings['smtp_encryption']) ? $settings['smtp_encryption'] : 'tls'; ?>
                <option value="ssl" <?php echo $enc === 'ssl' ? 'selected' : ''; ?>>SSL</option>
                <option value="tls" <?php echo $enc === 'tls' ? 'selected' : ''; ?>>TLS</option>
                <option value="none" <?php echo $enc === 'none' ? 'selected' : ''; ?>>None</option>
            </select>
        </label>

        <label>From Name
            <input type="text" name="smtp_from_name" value="<?php echo Utils::h(isset($settings['smtp_from_name']) ? $settings['smtp_from_name'] : 'CallTrack System'); ?>">
        </label>

        <label>Получатели уведомлений об инцидентах
            <textarea name="incident_recipients" rows="5" placeholder="admin@example.com, ops@example.com"><?php echo Utils::h(isset($settings['incident_recipients']) ? $settings['incident_recipients'] : ''); ?></textarea>
            <span class="help-text">Можно указать несколько адресов через запятую, пробел или с новой строки.</span>
        </label>

        <div class="form-actions">
            <button class="btn" type="submit">Сохранить настройки</button>
            <a class="btn btn-secondary" href="index.php?page=tickets">Назад</a>
        </div>
    </form>
</div>
