<?php

// uin 和 skey 的获取方法如下:
// 打开 QQ 空间网页，在控制台里执行下面的代码:
// document.cookie.match(/(uin|skey)=(.+?);/g);

// Usage:
// (new Tieba($bduss))->sign();
// (new Tieba)->setup($bduss)->sign();

require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/Tieba.php';

$tieba  = new Tieba;
$config = json_decode(file_get_contents('user.json'));

foreach ($config->tieba as $index => $user) {
	kvlog('开始签到', $user->name);
	$tieba->reset($user->bduss)->sign();
	kvlog('签到完成', $user->name);
	kvlog();
}
