<?php
include 'config.php';
include 'utils/helpers.php';
include 'utils/vk.php';
include 'utils/history.php';

// Подключаем все команды автоматически
foreach (glob('commands/*.php') as $file) include $file;
$all_functions = get_defined_functions()['user'];

// LongPoll подключение
$lp = vk_request("groups.getLongPollServer", ["group_id" => GROUP_ID])['response'];
$ts = $lp['ts'];

while (true) {
    $url = "{$lp['server']}?act=a_check&key={$lp['key']}&ts={$ts}&wait=25";
    $data = json_decode(file_get_contents($url), true);
    if (!isset($data['updates'])) continue;
    $ts = $data['ts'] ?? $ts;

    foreach ($data['updates'] as $upd) {
        if ($upd['type'] !== 'message_new') continue;
        $msg = $upd['object']['message'];

        $peer_id = $msg['peer_id'];
        $from_id = $msg['from_id'];

        // -------- Авто-кик забаненных при добавлении --------
        if (!empty($msg['action']) && $msg['action']['type'] === 'chat_invite_user') {
            $added_ids = [$msg['action']['member_id'] ?? 0];
            checkBannedOnJoin($peer_id, $added_ids);
        }

        // -------- Проверка мутов --------
        if (handleMutedMessage($msg)) continue; // Если сообщение удалено, команды не обрабатываем

        $text = trim($msg['text']);

        // -------- Игнорируем баннутых пользователей --------
        if (isBanned($from_id)) {
            sendMessage($peer_id, "❌ Вы заблокированы и не можете использовать команды.");
            continue;
        }

        // -------- Авто-вызов команд --------
        foreach ($all_functions as $func) {
            if (str_starts_with($func, 'cmd_')) {
                $cmd_name = '/' . substr($func, 4);
                if (str_starts_with($text, $cmd_name)) {
                    $args_text = trim(substr($text, strlen($cmd_name)));
                    $args = $args_text !== '' ? explode(' ', $args_text) : [];
                    $response = $func($from_id, $peer_id, $args);
                    sendMessage($peer_id, $response);
                    break;
                }
            }
        }
    }
}