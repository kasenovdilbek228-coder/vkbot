<?php
define('USERS_FILE', __DIR__ . '/../data/users.json');

function loadUsers() {
    if (!file_exists(USERS_FILE)) file_put_contents(USERS_FILE, json_encode([]));
    return json_decode(file_get_contents(USERS_FILE), true);
}

function saveUsers($users) {
    file_put_contents(USERS_FILE, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

function getOrCreateUser($vk_id) {
    $users = loadUsers();

    if (!isset($users[$vk_id])) {
        $users[$vk_id] = [
            "warns" => 0,
            "mute_until" => 0,
            "nick" => "",
            "rank" => "",
            "server_id" => 0
        ];
        saveUsers($users);
    }

    return $users[$vk_id];
}

function updateUser($vk_id, $data) {
    $users = loadUsers();
    $users[$vk_id] = $data;
    saveUsers($users);
}

function loadServers() {
    $file = __DIR__ . '/../data/servers.txt';
    $servers = [];

    if (!file_exists($file)) return $servers;

    $lines = file($file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        $spacePos = strpos($line, ' ');
        if ($spacePos === false) continue;

        $id = intval(substr($line, 0, $spacePos));
        $name = trim(substr($line, $spacePos + 1));

        if ($name !== '') $servers[$id] = $name;
    }

    return $servers;
}

// 🔑 Универсальное определение пользователя
function resolveUserId($arg) {
    $users = loadUsers();

    // Если упоминание вида [id123|Текст]
    if (preg_match("/^\[id(\d+)\|.*\]$/u", $arg, $m)) {
        return intval($m[1]);
    }

    // Если упоминание @id123
    if (preg_match("/^@(\d+)$/u", $arg, $m)) {
        return intval($m[1]);
    }

    // Если это число (просто VK ID)
    if (ctype_digit($arg)) {
        return intval($arg);
    }

    // Если ввели ник (ищем по базе users.json)
    foreach ($users as $vk_id => $data) {
        if (!empty($data['nick']) && mb_strtolower($data['nick']) === mb_strtolower($arg)) {
            return intval($vk_id);
        }
    }

    return null; // ❌ Не найден
}