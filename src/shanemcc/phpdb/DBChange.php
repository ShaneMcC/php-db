<?php

	namespace shanemcc\phpdb;

	class DBChange {
		protected $query = '';
		protected $result = null;

		public function __construct($query) {
			$this->query = $query;
		}

		public function run($pdo) {
			if ($pdo->exec($this->query) !== FALSE) {
				$this->result = TRUE;
				echo 'success', "\n";
			} else {
				$ei = $pdo->errorInfo();
				$this->result = $ei[2];
				echo 'failed', "\n";
			}

			return $this->getLastResult();
		}

		public function getLastResult() {
			return ($this->result === TRUE);
		}

		public function getLastError() {
			return ($this->result === TRUE) ? NULL : $this->result;
		}
	}
