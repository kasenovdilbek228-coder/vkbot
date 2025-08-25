<?php
function cmd_unban($from_id, $peer_id, $args) {
    if (empty($args)) return "❌ Укажите пользователя для разбана.";

    $target_id = resolveUserId($args[0]);
    if (!$target_id) return "❌ Пользователь '{$args[0]}' не найден.";

    $bans = loadBans();
    if (!isset($bans[$target_id])) return "❌ Пользователь не заблокирован.";

    // Снимаем бан
    unset($bans[$target_id]);
    saveBans($bans);

    // Обновляем историю
    if (function_exists('updateHistory')) {
        updateHistory($target_id, $peer_id, 'ban', [
            'resolved' => true,
            'resolved_time' => time()
        ]);
    }

    $user = getOrCreateUser($target_id);
    $nick = $user['nick'] ?: "Не установлен";

    return "✅ Пользователь [id{$target_id}|{$nick}] разблокирован.";
}