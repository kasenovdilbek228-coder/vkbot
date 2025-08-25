<?php
function cmd_mute($from_id, $peer_id, $args) {
    if (count($args) < 2) return "❌ Укажите пользователя и длительность.";

    $target_id = resolveUserId($args[0]);
    if (!$target_id) return "❌ Пользователь '{$args[0]}' не найден.";

    // Разбираем длительность: 10m, 2h, 1d
    $duration = $args[1];
    $seconds = 0;
    if (preg_match('/(\d+)([mhd])/', $duration, $m)) {
        $num = intval($m[1]);
        switch ($m[2]) {
            case 'm': $seconds = $num * 60; break;
            case 'h': $seconds = $num * 3600; break;
            case 'd': $seconds = $num * 86400; break;
        }
    } else {
        $seconds = intval($duration); // по умолчанию секунды
    }

    $reason = count($args) > 2 ? implode(' ', array_slice($args, 2)) : "Не указана";

    $user = getOrCreateUser($target_id);
    $user['mute_until'] = time() + $seconds;
    $user['mute_reason'] = $reason;
    $user['mute_warn_count'] = 0;
    updateUser($target_id, $user);

    addHistory($from_id, $target_id, 'mute', $reason, $seconds);

    $target_user = getOrCreateUser($target_id);
    $nick = $target_user['nick'] ?: "Не установлен";

    return "🔇 Пользователь [id{$target_id}|{$nick}] замучен на {$duration}.\nПричина: $reason";
}

// Функция для удаления сообщений замученных пользователей
function handleMutedMessage($msg) {
    $from_id = $msg['from_id'];
    $peer_id = $msg['peer_id'];
    $msg_id  = $msg['conversation_message_id']; // Обязательно conversation_message_id!

    $user = getOrCreateUser($from_id);
    if ($user['mute_until'] > time()) {
        // Удаляем сообщение
        vk_request("messages.delete", [
            "peer_id" => $peer_id,
            "conversation_message_ids" => $msg_id,
            "delete_for_all" => true
        ]);

        // Счетчик предупреждений
        if (!isset($user['mute_warn_count'])) $user['mute_warn_count'] = 0;
        $user['mute_warn_count']++;

        if ($user['mute_warn_count'] % 15 === 1) {
            $nick = $user['nick'] ?: "Не установлен";
            $reason = $user['mute_reason'] ?: "Не указана";
            sendMessage($peer_id, "⚠️ [id{$from_id}|{$nick}] пытается писать сообщение но имеет активный мут по причине: $reason");
        }

        updateUser($from_id, $user);
        return true; // Сообщение удалено
    }

    return false; // Можно обрабатывать дальше
}