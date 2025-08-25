<?php
function cmd_setrank($from_id, $peer_id, $args) {
    if (empty($args)) return "❌ Укажите должность.";

    // Если первый аргумент — ник/упоминание → меняем ранг другому
    if (count($args) > 1) {
        $target_id = resolveUserId($args[0]);
        if (!$target_id) return "❌ Пользователь '{$args[0]}' не найден.";
        // Всё остальное считаем как должность (может содержать пробелы)
        $rank = implode(' ', array_slice($args, 1));
    } else {
        $target_id = $from_id;
        $rank = $args[0];
    }

    $user = getOrCreateUser($target_id);
    $user['rank'] = $rank;
    updateUser($target_id, $user);

    return "✅ Должность успешно установлена: $rank";
}