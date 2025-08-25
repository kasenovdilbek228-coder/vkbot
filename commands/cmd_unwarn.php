<?php
function cmd_unwarn($from_id, $peer_id, $args) {
    if (empty($args)) return "❌ Укажите пользователя для снятия варна.";

    $target_id = resolveUserId($args[0]);
    if (!$target_id) return "❌ Пользователь '{$args[0]}' не найден.";

    $users = loadUsers();
    if (!isset($users[$target_id]) || $users[$target_id]['warns'] <= 0) {
        return "❌ У пользователя нет варнов.";
    }

    $users[$target_id]['warns'] = max(0, $users[$target_id]['warns'] - 1);
    updateUser($target_id, $users[$target_id]);

    if (function_exists('updateHistory')) {
        updateHistory($target_id, $peer_id, 'warn', [
            'resolved' => true,
            'resolved_time' => time()
        ]);
    }

    $nick = $users[$target_id]['nick'] ?: "Не установлен";
    return "✅ Варн пользователя [id{$target_id}|{$nick}] снят.";
}