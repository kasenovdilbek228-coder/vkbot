<?php
function vk_request($method, $params = []) {
    $params['access_token'] = 'vk1.a.1KfG-kpQzbxG8hHHpC2hO14NDWY_NOii-7noW08zWsl9CRjQFA_TG5WeungyvIEQ1WCxYppjNEvcWMM2XmXgDu8ReEtAhCgcEKlrfqvvOaHGBCnMxWtmyOh8IqChIf_lMptR4mnf_xcCcyKK_M8BXXAN9w4kyPYMQ6LYK2jWSc21czUlqt9skRhQE5XbxCYhO-0Gyn2e8vr5wZTsl-zNXw';
    $params['v'] = '5.131';
    $url = "https://api.vk.com/method/$method?" . http_build_query($params);
    $res = file_get_contents($url);
    return json_decode($res, true);
}

function sendMessage($peer_id, $message) {
    vk_request("messages.send", [
        'peer_id' => $peer_id,
        'message' => $message,
        'random_id' => rand(1, 999999)
    ]);
}