<?php

namespace kissj\User;


class UserStatusService {
	private $statuses;
	
	public function __construct() {
		$this->statuses = [
			'dataFill',
			'closed',
			'aprooved',
			'paid'];
	}
	
	public function getFirstStatus($role): string {
		// TODO enhance for different roles
		return $this->statuses[0];
	}
	
	/**
	 * @param $role
	 * @return int
	 * @throws \Exception
	 */
	public function getNextStatus($role): string {
		if (!$this->isStatusValid($role)) {
			throw new \Exception('Unknown status "'.$role.'"');
		}
		if ($this->isStatusLast($role)) {
			throw new \Exception('Last role in row');
		}
		
		$key = array_search($role, $this->statuses);
		
		return $this->statuses[$key + 1];
	}
	
	public function isStatusValid($status): bool {
		return in_array($status, $this->statuses);
	}
	
	public function isStatusLast($role):bool {
		return $role == end($this->statuses);
	}
}