<?php
define('HISTORY_FILE', __DIR__ . '/../data/history.json');

function loadHistory() {
    if (!file_exists(HISTORY_FILE)) file_put_contents(HISTORY_FILE, json_encode([]));
    return json_decode(file_get_contents(HISTORY_FILE), true);
}

function saveHistory($history) {
    file_put_contents(HISTORY_FILE, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

/**
 * Добавляет запись в историю наказаний
 * $from_id – админ
 * $peer_id – чат
 * $type – тип наказания (warn, mute, kick, ban)
 * $target_id – пользователь
 * $reason – текст причины
 */
function addHistory($from_id, $peer_id, $type, $target_id, $reason) {
    $history = loadHistory();

    if (!isset($history[$peer_id])) $history[$peer_id] = [];

    $history[$peer_id][] = [
        'time' => time(),
        'by_id' => $from_id,
        'target_id' => $target_id,
        'type' => $type,
        'reason' => $reason,
        'resolved' => false // false – активно, true – снято
    ];

    saveHistory($history);
}

/**
 * Снимает наказание (unwarn/unmute/unban)
 */
function resolveHistory($peer_id, $target_id, $type, $reason = null) {
    $history = loadHistory();
    if (!isset($history[$peer_id])) return;

    foreach ($history[$peer_id] as &$h) {
        if ($h['target_id'] == $target_id && $h['type'] == $type && !$h['resolved']) {
            $h['resolved'] = true;
            if ($reason) $h['reason'] .= " | Снято: $reason";
        }
    }

    saveHistory($history);
}

/**
 * Получить историю по чату
 */
function getHistory($peer_id) {
    $history = loadHistory();
    return $history[$peer_id] ?? [];
}