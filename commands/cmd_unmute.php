<?php
function cmd_unmute($from_id, $peer_id, $args) {
    if (empty($args)) return "❌ Укажите пользователя для снятия мута.";

    $target_id = resolveUserId($args[0]);
    if (!$target_id) return "❌ Пользователь '{$args[0]}' не найден.";

    $users = loadUsers();
    if (!isset($users[$target_id]) || $users[$target_id]['mute_until'] <= time()) {
        return "❌ Пользователь не в муте.";
    }

    $users[$target_id]['mute_until'] = 0;
    updateUser($target_id, $users[$target_id]);

    if (function_exists('updateHistory')) {
        updateHistory($target_id, $peer_id, 'mute', [
            'resolved' => true,
            'resolved_time' => time()
        ]);
    }

    $nick = $users[$target_id]['nick'] ?: "Не установлен";
    return "✅ Мут пользователя [id{$target_id}|{$nick}] снят.";
}