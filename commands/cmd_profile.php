<?php
function cmd_profile($from_id, $peer_id, $args) {
    // Если аргументов нет → показываем профиль самого себя
    if (empty($args)) {
        $target_id = $from_id;
    } else {
        $target_id = resolveUserId($args[0]);
        if (!$target_id) {
            return "❌ Пользователь '{$args[0]}' не найден.";
        }
    }

    $user = getOrCreateUser($target_id);
    $servers = loadServers();
    $server_name = $servers[$user['server_id']] ?? "Не выбран";

    $nick = $user['nick'] ?: "Не установлен";
    $rank = $user['rank'] ?: "Нет";
    $emsp = "\u{2003}";

    return "📄 Основная информация о пользователе {$emsp}\n" .
           "👤 Ник: [id{$target_id}|{$nick}]\n" .
           "💼 Должность: $rank\n" .
           "🌍 Сервер: $server_name [ID:{$user['server_id']}]\n" .
           "📄 Наказания в беседе:\n" .
           "⚠️ Предупреждения: {$user['warns']} из 3\n" .
           "🔇 Мут: " . ($user['mute_until'] > time() ? "До " . date("d.m H:i", $user['mute_until']) : "Нет");
}