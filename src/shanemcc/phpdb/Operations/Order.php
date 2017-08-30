<?php

	namespace shanemcc\phpdb\Operations;

	class Order extends DBOperation {
		private $key;
		private $direction;

		/**
		 * Create an order clause.
		 *
		 * @param $key Key to order by
		 * @param $direction Direction to order (Default: 'ASC')
		 */
		public function __construct($key, $direction = 'ASC') {
			$this->key = $key;
			$this->direction = $direction;
		}

		public function __toString() {
			return sprintf('`%s` %s', $this->key, $this->direction);
		}

		public static function operation() { return 'ORDER BY'; }
	}
