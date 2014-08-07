<?php

namespace GitHub;

use Nette;
use Tracy;


class GitHub
{

	/** @var string */
	private $clientID;

	/** @var string */
	private $secret;


	/**
	 * @param  string $clientID
	 * @param  string $secret
	 */
	function __construct($clientID, $secret)
	{
		$this->clientID = (string) $clientID;
		$this->secret = (string) $secret;
	}


	/**
	 * @param  string $name
	 * @return mixed
	 */
	function getRepository($name)
	{
		return $this->call('repos/' . $name);
	}


	/**
	 * @param  string $owner
	 * @param  string $repository
	 * @param  string $sha
	 * @return mixed
	 */
	function getCommit($owner, $repository, $sha)
	{
		return $this->call('repos/' . $owner . '/' . $repository . '/commits/' . $sha);
	}


	/**
	 * @param  string $login
	 * @return mixed
	 */
	function getUser($login)
	{
		return $this->call('users/' . $login);
	}


	/**
	 * @param  string $owner
	 * @param  string $repository
	 * @param  int $number
	 * @return mixed
	 */
	function getIssue($owner, $repository, $number)
	{
		return $this->call('repos/' . $owner . '/' . $repository . '/issues/' . $number);
	}


	/**
	 * @param  string $owner
	 * @param  string $repository
	 * @param  int $page
	 * @param  int $perpage
	 * @return mixed
	 */
	function getCommits($owner, $repository, $page, $perpage)
	{
		return $this->call('repos/' . $owner . '/' . $repository . '/commits', array(
			'page' => $page,
			'per_page' => $perpage,
		));
	}


	/**
	 * @param  $response
	 * @return bool
	 */
	static function success($response)
	{
		return $response instanceof \stdClass && !isset($response->message);
	}


	/**
	 * @param  string $api
	 * @param  array $query
	 * @return mixed
	 */
	private function call($api, array $query = NULL)
	{
		static $ch = NULL;

		if ($ch === NULL) {
			$ch = curl_init();
			curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
			curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
			curl_setopt($ch, CURLOPT_HTTPHEADER, array('User-Agent: Mozilla/5.0 (Windows NT 6.1; WOW64)'));
		}

		$url = new Nette\Http\Url('https://api.github.com/' . $api . '?client_id=' . $this->clientID . '&client_secret=' . $this->secret);

		if ($query !== NULL) {
			foreach ($query as $key => $val) {
				$url->setQueryParameter($key, $val);
			}
		}

		curl_setopt($ch, CURLOPT_URL, (string) $url);

		$res = curl_exec($ch);
		if (curl_errno($ch) !== 0) {
			Tracy\Debugger::log(curl_error($ch));
		}

		return Nette\Utils\Json::decode($res);
	}

}
