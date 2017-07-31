<?php

	namespace shanemcc\phpdb;
	use PDO;
	use Exception;

	class DB {
		private static $instance = null;

		private $pdo = null;

		public static function get() {
			if (self::$instance == null) {
				self::$instance = new DB();
			}

			return self::$instance;
		}

		public function setPDO($pdo) {
			$this->pdo = $pdo;
		}

		public function getPDO() {
			return $this->pdo;
		}

		public function tableExists($table) {
			try {
				$result = $this->pdo->query("SELECT 1 FROM $table LIMIT 1");
			} catch (Exception $e) {
				return FALSE;
			}
			return $result !== FALSE;
		}

		public function getMetaData($key, $default = FALSE) {
			if (!$this->tableExists('__MetaData')) {
				return $default;
			}

			$query = "SELECT `value` FROM `__MetaData` WHERE `key` = :key";
			$params = array(":key" => $key);

			$statement = $this->pdo->prepare($query);
			$statement->execute($params);
			$result = $statement->fetch(PDO::FETCH_ASSOC);

			if ($result) {
				return $result['value'];
			} else {
				return $default;
			}
		}

		public function setMetaData($key, $value) {
			$driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

			if ($driver == 'sqlite') {
				$query = "INSERT OR REPLACE INTO `__MetaData` (`key`, `value`) VALUES (:key, :value)";
			} else {
				$query = "INSERT INTO `__MetaData` (`key`, `value`) VALUES (:key, :value)";
			}
			$params = array(":key" => $key, ":value" => $value);

			$statement = $this->pdo->prepare($query);
			$result = $statement->execute($params);
			return $result;
		}

		public function getLastError() {
			return $this->pdo->errorInfo();
		}

		function runChanges($changer) {
			$changes = $changer->getChanges();
			$versionField = $changer->getVersionField();

			$currentVersion = (int)$this->getMetaData($versionField, 0);

			echo $versionField, ' Current Version: ', $currentVersion, "\n";

			foreach ($changes as $version => $change) {
				if ($version <= $currentVersion) { continue; }
				echo $versionField, ' Updating to version ', $version, ': ';

				if ($change->run($this->getPDO())) {
					$this->setMetaData($versionField, $version);
					$currentVersion = $version;
				} else {
					echo "\n", 'Error updating to version ', $version, ': ', $change->getLastError(), "\n";
					return $currentVersion;
				}
			}

			return $currentVersion;
		}
	}
