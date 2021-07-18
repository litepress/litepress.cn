<?php


class wfHelperString {

	/**
	 * cycle through arguments
	 *
	 * @return mixed
	 */
	public static function cycle() {
		static $counter = 0;
		$args = func_get_args();
		if (empty($args)) {
			$counter = 0;
			return null;
		}
		$return_val = $args[$counter % count($args)];
		$counter++;
		return $return_val;
	}

	public static function plainTextTable($table) {
		if (count($table) === 0) {
			return '';
		}
		$colLengths = array();
		for ($row = 0; $row < count($table); $row++) {
			for ($col = 0; $col < count($table[$row]); $col++) {
				foreach (explode("\n", $table[$row][$col]) as $colText) {
					if (!isset($colLengths[$col])) {
						$colLengths[$col] = strlen($colText);
						continue;
					}
					$len = strlen($colText);
					if ($len > $colLengths[$col]) {
						$colLengths[$col] = $len;
					}
				}
			}
		}
		$hr = str_repeat('-', array_sum($colLengths) + (count($colLengths) * 3) + 1);
		$output = $hr . "\n";
		for ($row = 0; $row < count($table); $row++) {
			$colHeight = 0;
			for ($col = 0; $col < count($table[$row]); $col++) {
				$height = substr_count($table[$row][$col], "\n");
				if ($height > $colHeight) {
					$colHeight = $height;
				}
			}
			for ($colRow = 0; $colRow <= $colHeight; $colRow++) {
				for ($col = 0; $col < count($table[$row]); $col++) {
					$colRows = explode("\n", $table[$row][$col]);
					$output .= '| ' . str_pad(isset($colRows[$colRow]) ? $colRows[$colRow] : '', $colLengths[$col], ' ', STR_PAD_RIGHT) . ' ';
				}
				$output .= "|\n";
			}
			if ($row === 0) {
				$output .= $hr . "\n";
			}
		}
		return trim($output . (count($table) > 1 ? $hr : ''));
	}
}