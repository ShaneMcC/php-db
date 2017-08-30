<?php

	namespace shanemcc\phpdb\Operations;

	class OrderByFunction extends Order {
		private $function;
		private $key;
		private $direction;

		/**
		 * Create an order clause.
		 *
		 * @param $function Function to apply
		 * @param $key Key to order by
		 * @param $direction Direction to order (Default: 'ASC')
		 */
		public function __construct($function, $key, $direction = 'ASC') {
			$this->function = $function;
			$this->key = $key;
			$this->direction = $direction;
		}

		public function __toString() {
			return sprintf('%s(`%s`) %s', $this->function, $this->key, $this->direction);
		}
	}
