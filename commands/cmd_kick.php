<?php
function cmd_kick($from_id, $peer_id, $args) {
    if (!isset($args[0])) return "❌ Укажите пользователя.";
    $target_id = resolveUserId($args[0]);
    if (!$target_id) return "❌ Пользователь не найден.";

    $reason = count($args) > 1 ? implode(' ', array_slice($args, 1)) : "Не указана";

    vk_request("messages.removeChatUser", [
        "chat_id" => $peer_id - 2000000000,
        "user_id" => $target_id
    ]);

    addHistory($from_id, $peer_id, 'kick', $target_id, $reason);

    $user = getOrCreateUser($target_id);
    return "👢 Пользователь [id{$target_id}|{$user['nick']}] кикнут. Причина: $reason";
}