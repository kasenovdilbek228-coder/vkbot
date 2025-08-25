<?php
function cmd_warn($from_id, $peer_id, $args) {
    if (!isset($args[0])) return "❌ Укажите пользователя.";
    $target_id = resolveUserId($args[0]);
    if (!$target_id) return "❌ Пользователь не найден.";

    $reason = count($args) > 1 ? implode(' ', array_slice($args, 1)) : "Не указана";

    // Добавляем варн
    $user = getOrCreateUser($target_id);
    $user['warns'] = ($user['warns'] ?? 0) + 1;
    updateUser($target_id, $user);

    // Запись в историю
    addHistory($from_id, $peer_id, 'warn', $target_id, $reason);

    return "⚠️ Пользователю [id{$target_id}|{$user['nick']}] выдан варн. Причина: $reason";
}