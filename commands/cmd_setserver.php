<?php
function cmd_setserver($from_id, $peer_id, $args) {
    if (empty($args)) return "❌ Укажите ID сервера.";

    // Если первый аргумент — ник/упоминание → меняем сервер другому
    if (count($args) > 1) {
        $target_id = resolveUserId($args[0]);
        if (!$target_id) return "❌ Пользователь '{$args[0]}' не найден.";
        $server_id = intval($args[1]);
    } else {
        $target_id = $from_id;
        $server_id = intval($args[0]);
    }

    $user = getOrCreateUser($target_id);
    $user['server_id'] = $server_id;
    updateUser($target_id, $user);

    $servers = loadServers();
    $server_name = $servers[$server_id] ?? "Неизвестный сервер";

    return "✅ Сервер успешно установлен: $server_name [ID:$server_id]";
}