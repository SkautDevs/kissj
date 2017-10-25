<?php

/**
 * Created by PhpStorm.
 * User: Azathoth
 * Date: 2017-10-25
 * Time: 20:22
 */
class ParticipantService {

	/** @var PDO */
	private $conn;

	public function __construct(PDO $conn) {
		$this->conn = $conn;
	}

	public function addParticipant() {

	}

}