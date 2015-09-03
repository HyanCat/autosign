<?php

class QQGroup {

	const SignUrl    = 'http://qiandao.qun.qq.com/cgi-bin/sign';
	const QunListUrl = 'http://qun.qzone.qq.com/cgi-bin/get_group_list?uin=%s&g_tk=%s';

	protected $qq;
	protected $cookie;
	protected $gtk;

	public function __construct($uin = null, $skey = null) {
		if (!is_null($uin) && !is_null($skey)) {
			$this->setup($uin, $skey);
		}
	}
	/**
	 * 设置 uin 和 skey
	 */
	public function setup($uin, $skey) {
		$this->qq     = preg_replace('/^o0*/', '', $uin); // 数字QQ号码
		$this->cookie = sprintf('Cookie: uin=%s; skey=%s;', $uin, $skey); // Cookie
		$this->gtk    = $this->getGTK($skey); // 计算 g_tk
		kvlog('QQ', $this->qq);
		kvlog('cookie', $this->cookie);
		kvlog('gtk', $this->gtk);
		kvlog();
		return $this;
	}

	/**
	 * 重置 uin 和 skey
	 */
	public function reset($uin, $skey) {
		$this->setup($uin, $skey);
		return $this;
	}

	/**
	 * 签到所有群
	 */
	public function sign() {
		$this->signGroups($this->getGroupList()); //获取群列表并签到
		return $this;
	}

	/**
	 * 获取群列表
	 */
	private function getGroupList() {
		$html = @file_get_contents(
			sprintf(self::QunListUrl, $this->qq, $this->gtk),
			false,
			stream_context_create(array(
				'http' => array(
					'method' => 'GET',
					'header' => $this->cookie,
				),
			))
		);
		preg_match('/(\{[\s\S]+\})/', $html, $qunList);

		if (count($qunList) == 0) {
			kvlog('Error', '获取群列表失败');
			return NULL;
		}

		$qunList = json_decode($qunList[1]);

		if ($qunList == NULL || $qunList->code != 0) {
			kvlog('Error', '获取群列表失败');
			return NULL;
		}

		return $qunList->data->group;
	}

	/**
	 * 批量群签到
	 */
	private function signGroups($groups) {
		if ($groups == NULL) {
			kvlog('Error', '群列表为空');
			return;
		}

		foreach ($groups as $index => $qun) {
			$this->signGroup($qun->groupid); //签到
			kvlog($index + 1, sprintf("%s(%d)", $qun->groupname, $qun->groupid) . "\tok");
		}
	}

	/**
	 * 签到某个群
	 * @param  $groupId 群 ID
	 */
	private function signGroup($groupId) {
		@file_get_contents(self::SignUrl, false,
			stream_context_create(
				array('http' => array(
					'method'  => 'POST',
					'header'  => $this->cookie,
					'content' => sprintf('gc=%s&is_sign=0&bkn=%s', $groupId, $this->gtk),
				))
			)
		);
	}

	private function getGTK($skey) {
		$len  = strlen($skey);
		$hash = 5381;

		for ($i = 0; $i < $len; $i++) {
			$hash += ($hash << 5) + ord($skey[$i]);
		}

		return $hash & 0x7fffffff; //计算g_tk
	}
}
