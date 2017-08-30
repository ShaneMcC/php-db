<?php

	namespace shanemcc\phpdb\Operations;

	class Join extends DBOperation {
		private $table;
		private $on;
		private $direction;

		/**
		 * Create an order clause.
		 *
		 * @param $table Table to join to
		 * @param $on (Default: none) Statement to join on
		 * @param $direction (Default: none) LEFT/RIGHT join.
		 */
		public function __construct($table, $on = null, $direction = null) {
			$this->table = $table;
			$this->on = $on;
			$this->direction = $direction;
		}

		public function __toString() {
			$result = '';
			// Join Direction
			if (!empty($this->direction)) {
				$result .= sprintf('%s ', $this->direction);
			}

			// Join table
			$result .= sprintf('JOIN %s', $this->table);

			// Join on
			if (!empty($this->on)) {
				$result .= sprintf(' ON %s', $this->on);
			}

			return $result;
		}

		public static function operation() { return 'JOIN'; }
	}
