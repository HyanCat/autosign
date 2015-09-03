<?php

// uin 和 skey 的获取方法如下:
// 打开 QQ 空间网页，在控制台里执行下面的代码:
// document.cookie.match(/(uin|skey)=(.+?);/g);

// Usage:
// (new QQGroup($uin, $skey))->sign();
// (new QQGroup)->setup($uin, $skey)->sign();

require_once __DIR__ . '/helper.php';
require_once __DIR__ . '/QQGroup.php';

$group  = new QQGroup;
$config = json_decode(file_get_contents('user.json'));

foreach ($config->qqgroup as $index => $user) {
	kvlog('开始签到', $user->name);
	$group->reset($user->uin, $user->skey)->sign();
	kvlog('签到完成', $user->name);
	kvlog();
}
