<?php

namespace kissj;

use PDO;

class ParticipantService {

	/** @var PDO */
	private $conn;

	public function __construct(PDO $conn) {
		$this->conn = $conn;
	}

	public function addParticipant() {

	}

}