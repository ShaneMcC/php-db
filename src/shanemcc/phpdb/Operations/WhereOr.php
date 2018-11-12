<?php

	namespace shanemcc\phpdb\Operations;

	class WhereOr extends DBOperation {
		private $operations;

		/**
		 * Create an order clause.
		 *
		 * @param $operations Array or operations.
		 */
		public function __construct($operations) {
			$this->operations = $operations;
			$i = 0;
			foreach ($operations as $op) {
				$op->build('or' . $i++ . '_');
			}
		}

		public function getParams() {
			$params = [];

			foreach ($this->operations as $op) {
				$params = array_merge($params, $op->getParams());
			}

			return $params;
		}

		public function __toString() {

			$string = '(' . implode(' OR ', $this->operations) . ')';

			return $string;
		}

		public static function operation() { return 'WHERE'; }
	}
