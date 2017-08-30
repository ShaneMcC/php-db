<?php

	namespace shanemcc\phpdb\Operations;

	class Limit extends DBOperation {
		private $limit;
		private $offset;

		/**
		 * Create an order clause.
		 *
		 * @param $limit Limit returned results.
		 * @param $offset (Optional) Optional offset.
		 */
		public function __construct($limit, $offset = FALSE) {
			$this->limit = $limit;
			$this->offset = $offset;
		}

		public function __toString() {
			if ($this->offset === FALSE) {
				return sprintf(' LIMIT %d', $this->limit);
			} else {
				return sprintf(' LIMIT %d,%d', $this->limit, $this->offset);
			}
		}

		public static function operation() { return 'LIMIT'; }
	}
