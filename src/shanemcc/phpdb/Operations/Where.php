<?php

	namespace shanemcc\phpdb\Operations;

	class Where extends DBOperation {
		private $key;
		private $comparator;
		private $value;

		/*
		 * Create a where item.
		 *
		 * @param $key Key to search for.
		 * @param $value Value to search for.
		 * @param $comparator (Default: '=') Comparator to use.
		 */
		public function __construct($key, $value, $comparator = null) {
			$this->key = $key;
			$this->comparator = $comparator;
			$this->value = $value;

			list($w, $p) = $this->build();
			$this->where = $w;
			$this->params = $p;
		}

		public function __toString() {
			return $this->where;
		}

		public function getParams() {
			return $this->params;
		}

		private function build() {
			$where = '';
			$params = [];

			// If value is an array, then we can do OR or use IN.
			if (is_array($this->value)) {
				$arrayWhere = [];
				// Use IN for '=' or != or null comparators
				$useIN = ($this->comparator === null || $this->comparator == '=' || $this->comparator == '!=');

				// PDO doesn't support arrays, so we need to break it out
				// into separate params and expand the query to include
				// these params.
				for ($i = 0; $i < count($this->value); $i++) {
					// PDO-Friendly param name.
					$params[sprintf(':%s_%d', $this->key, $i)] = $this->value[$i];

					// If we're using IN then we just generate an array of
					// parameters, else generate the usual <key> <comparator> <param>
					if ($useIN) {
						$arrayWhere[] = sprintf(':%s_%d', $this->key, $i);
					} else {
						$arrayWhere[] = sprintf('`%s` %s :%s_%d', $this->key, $this->comparator, $this->key, $i);
					}
				}

				// Either build:
				//    <key> [NOT] IN (<params>)
				//    (<key> <comparator> <value> OR <key> <comparator> <value> OR <key> <comparator> <value> ... )
				if ($useIN) {
					if ($this->comparator == '!=') {
						$where = sprintf('`%s` NOT IN (%s)', $this->key, implode(', ', $arrayWhere));
					} else {
						$where = sprintf('`%s` IN (%s)', $this->key, implode(', ', $arrayWhere));
					}
				} else {
					$where = '(' . implode(' OR ', $arrayWhere) . ')';
				}
			} else {
				// Not an array, a nice simple <key> <comparator> <value> bit!
				$where = sprintf('`%s` %s :%s', $this->key, ($this->comparator === null ? '=' : $this->comparator), $this->key);
				$params[':' . $this->key] = $this->value;
			}

			return [$where, $params];
		}

		public static function operation() { return 'WHERE'; }
	}
