<?php

	namespace shanemcc\phpdb\Operations;

	class Select extends DBOperation {
		private $table;
		private $key;
		private $as;

		/**
		 * Create an entry to select.
		 *
		 * @param $table Table key is in.
		 * @param $key Key is in.
		 * @param $as (Default: $key) name to select this column as
		 */
		public function __construct($table, $key, $as = null) {
			if ($as === null) { $as = $key; }

			$this->table = $table;
			$this->key = $key;
			$this->as = $as;
		}

		public function __toString() {
			return sprintf('`%s`.`%s` AS `%s`', $this->table, $this->key, $this->as);
		}

		public static function operation() { return 'SELECT'; }
	}
