<?php
class Audit {
    public static function log($entityType, $entityId, $action, $field, $value) {
        $userId = Auth::id();
        Database::execute('INSERT INTO audit_logs (entity_type, entity_id, action, field, value, user_id, created_at) VALUES (?, ?, ?, ?, ?, ?, NOW())',
            array($entityType, $entityId, $action, $field, $value, $userId));
    }

    public static function logChanges($entityType, $entityId, $oldRow, $newRow) {
        if (!$oldRow || !$newRow) return;
        foreach ($newRow as $k => $v) {
            $old = isset($oldRow[$k]) ? $oldRow[$k] : null;
            if ($old !== $v && !in_array($k, array('updated_at'))) {
                self::log($entityType, $entityId, 'updated', $k, $v);
            }
        }
    }
}

