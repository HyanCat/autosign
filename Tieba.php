<?php

require 'vendor/autoload.php';

class Tieba {

	const TBS_URL       = "http://tieba.baidu.com/dc/common/tbs";
	const SIGN_URL      = "http://tieba.baidu.com/c/c/forum/sign";
	const POST_URL      = "http://tieba.baidu.com/c/c/post/add";
	const MSIGN_URL     = "http://tieba.baidu.com/c/c/forum/msign";
	const ADDTHREAD_URL = "http://tieba.baidu.com/c/c/thread/add";
	const FAV_URL       = "http://tieba.baidu.com/c/f/forum/like";

	protected $headers = [];
	protected $results = [];

	public function __construct($bduss = null) {
		if (!is_null($bduss)) {
			$this->setup($bduss);
		}
	}

	/**
	 * 设置 BDUSS
	 */
	public function setup($bduss) {
		$this->headers = [
			'Cookie'     => 'BDUSS=' . $bduss,
			'Host'       => 'tieba.baidu.com',
			'Referer'    => 'http://tieba.baidu.com/',
			'User-Agent' => 'Mozilla/5.0 (Linux; U; Android 4.4.4; zh-cn; HTC D820u Build/KTU84P) AppleWebKit/534.24 (KHTML, like Gecko) Version/4.0 Mobile Safari/534.24 T5/2.0 baidubrowser/5.3.4.0 (Baidu; P1 4.4.4)',
		];
		return $this;
	}

	/**
	 * 重置 BDUSS
	 */
	public function reset($bduss) {
		$this->setup($bduss);
		return $this;
	}

	/**
	 * 全部签到
	 */
	public function sign() {
		$tiebaForums = $this->getFavForums();
		$this->signTiebaForums($tiebaForums);
		return $this;
	}

	/**
	 * 批量签到
	 */
	public function signTiebaForums(array $tiebaForums) {
		foreach ($tiebaForums as $index => $forum) {
			$kw = $forum['name'];
			$this->signTieba($kw);
			kvlog($index + 1, $kw . "\tok");
		}
		return $this;
	}

	/**
	 * 签到贴吧
	 * @param string $kw <贴吧名>
	 */
	public function signTieba($kw) {
		$params   = ['kw' => $kw, 'tbs' => $this->getTbs()];
		$response = Requests::get(self::SIGN_URL . "?" . $this->encrypt($params), $this->headers);
		$response = json_decode($response->body);
		$result   = new StdClass;

		if ($response->error_code != 0) {
			$result->status = false;
			$result->msg    = $response->error_msg;
		} else {
			$result->status = true;
			$result->msg    = 'ok';
		}
		$result->kw      = $kw;
		$this->results[] = $result;

		return $result;
	}

	/**
	 * 查看返回结果，如果需要的话
	 * @param string $kw <贴吧名（不指定则返回所有结果）>
	 */
	public function getResult($kw = null) {
		if (is_null($kw)) {
			return $this->results;
		}
		foreach ($this->results as $index => $result) {
			if ($result->kw == $kw) {
				return $result;
			}
		}
	}

	protected function getFavForums() {
		$params   = ['tbs' => $this->getTbs()];
		$response = Requests::get(self::FAV_URL . "?" . $this->encrypt($params), $this->headers);
		$response = json_decode($response->body, true);
		return $response['forum_list'];
	}

	protected function getTbs() {
		$response = Requests::get(self::TBS_URL, $this->headers);
		$response = json_decode($response->body, true);

		return $response['tbs'];
	}

	private function encrypt($s) {
		ksort($s);
		$a = '';
		$b = '';
		foreach ($s as $j => $i) {
			$a .= $j . '=' . $i;
			$b .= $j . '=' . urlencode($i) . '&';
		};
		$a = strtoupper(md5($a . 'tiebaclient!!!'));

		return $b . 'sign=' . $a;
	}
}

?>