<?php
function cmd_listban($from_id, $peer_id, $args) {
    $bans = loadBans();
    if (empty($bans)) {
        return "Список забаненных пуст.";
    }

    $lines = [];
    foreach ($bans as $vk_id => $ban) {
        if ($ban['peer_id'] != $peer_id) continue; // Только для текущего чата
        $user = getOrCreateUser($vk_id);
        $nick = $user['nick'] ?: "Не установлен";
        $reason = $ban['reason'] ?: "Не указана";
        $lines[] = "[id{$vk_id}|{$nick}] — $reason";
    }

    if (empty($lines)) return "В этом чате забаненных нет.";

    return "📛 Список забаненных в этом чате:\n" . implode("\n", $lines);
}