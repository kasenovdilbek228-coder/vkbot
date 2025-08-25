<?php
define('BANS_FILE', __DIR__ . '/../data/bans.json');

function loadBans() {
    if (!file_exists(BANS_FILE)) file_put_contents(BANS_FILE, json_encode([]));
    return json_decode(file_get_contents(BANS_FILE), true);
}

function saveBans($bans) {
    file_put_contents(BANS_FILE, json_encode($bans, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function isBanned($vk_id) {
    $bans = loadBans();
    return isset($bans[$vk_id]);
}

// Команда /ban
function cmd_ban($from_id, $peer_id, $args) {
    if (!isset($args[0])) return "❌ Укажите пользователя.";
    $target_id = resolveUserId($args[0]);
    if (!$target_id) return "❌ Пользователь не найден.";

    $reason = count($args) > 1 ? implode(' ', array_slice($args, 1)) : "Не указана";

    vk_request("messages.removeChatUser", [
        "chat_id" => $peer_id - 2000000000,
        "user_id" => $target_id
    ]);

    $bans = loadBans();
    $bans[$target_id] = [
        'peer_id' => $peer_id,
        'reason' => $reason,
        'by' => $from_id,
        'timestamp' => time()
    ];
    saveBans($bans);

    addHistory($from_id, $peer_id, 'ban', $target_id, $reason);

    $user = getOrCreateUser($target_id);
    return "⛔ Пользователь [id{$target_id}|{$user['nick']}] заблокирован. Причина: $reason";
}

// Проверка забаненных при добавлении в чат
function checkBannedOnJoin($peer_id, $added_ids) {
    $bans = loadBans();
    $chat_id = $peer_id - 2000000000;

    foreach ($added_ids as $vk_id) {
        if ($vk_id <= 0) continue;
        if (!isset($bans[$vk_id])) continue;
        if ($bans[$vk_id]['peer_id'] != $peer_id) continue;

        // Кик
        vk_request("messages.removeChatUser", [
            "chat_id" => $chat_id,
            "user_id" => $vk_id
        ]);

        // Сообщение с ником и причиной
        $user = getOrCreateUser($vk_id);
        $nick = $user['nick'] ?: "Не установлен";
        $reason = $bans[$vk_id]['reason'] ?? "Не указана";
        sendMessage($peer_id, "⛔ Пользователь [id{$vk_id}|{$nick}] был кикнут. Имеет активную блокировку в чате по причине: $reason");
    }
}