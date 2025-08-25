<?php
function cmd_history($from_id, $peer_id, $args) {
    // Если $args массив, склеиваем в строку
    if (is_array($args)) $args = implode(' ', $args);

    $history = getHistory($peer_id);
    if (empty($history)) return "История наказаний в этом чате пуста.";

    // Если указан пользователь (@ник или ID), фильтруем
    $target_id = $args ? resolveUserId($args) : null;

    $lines = [];
    foreach ($history as $h) {
        if ($target_id && $h['target_id'] != $target_id) continue;

        $time = date("d.m H:i", $h['time']);
        $by_user = getOrCreateUser($h['by_id']);
        $by_nick = $by_user['nick'] ?: "Не установлен";

        $target_user = getOrCreateUser($h['target_id']);
        $target_nick = $target_user['nick'] ?: "Не установлен";

        $type = ucfirst($h['type']);

        // Обрабатываем причину, если это массив
        $reason = $h['reason'] ?? "Не указана";
        if (is_array($reason)) $reason = implode(', ', $reason);

        $lines[] = "[{$time}] {$by_nick} выдал {$type} {$target_nick}, причина: {$reason}";
    }

    return $lines ? implode("\n", $lines) : "История наказаний для данного пользователя пуста.";
}