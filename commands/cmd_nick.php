<?php
function cmd_nick($from_id, $peer_id, $args) {
    if (empty($args)) return "❌ Укажите ник.";

    // Проверяем, есть ли второй аргумент (ник для другого пользователя)
    if (count($args) == 1) {
        $target_id = $from_id;
        $nick = $args[0];
    } else {
        $target_id = resolveUserId($args[0]);
        if (!$target_id) return "❌ Пользователь '{$args[0]}' не найден.";
        $nick = $args[1];
    }

    $user = getOrCreateUser($target_id);
    $user['nick'] = $nick;
    updateUser($target_id, $user);

    return "✅ Ник успешно установлен: $nick";
}