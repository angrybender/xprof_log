<?php
/**
 *
 * @author k.vagin
 */

class Get_php_file_view
{
	public function get(array $get)
	{
		$line_n = (int)$get['line'];
		$source = file_get_contents(urldecode($get['full_path']));
		$source =  highlight_string($source, 1);

		$eol = '<br />'; //$this->get_eol_of_text($source);
		$lines = explode($eol, $source);

		foreach ($lines as $i => $line) {

			if ($i == 0) {
				$line = str_replace('<code>', '', $line);
			}
			else {
				$line = str_replace('</code>', '', $line);
			}

			if ($i+1 === $line_n) {
				$line = '<code class="line curr-line"><span class="line-number">' . ($i+1) . '</span>' . $line . '</code>';
			}
			else {
				$line = '<code class="line"><span class="line-number">' . ($i+1) . '</span>' . $line . '</code>';
			}

			$lines[$i] = $line;
		}

		return array(
			'file' => join(PHP_EOL, $lines)
		);
	}

	/**
	 * определяет символ переноса в файле
	 * @param string
	 * @return string
	 */
	private function get_eol_of_text($string)
	{
		$eol1 = substr_count($string, "\r");
		$eol2 = substr_count($string, "\r\n");
		$eol3 = substr_count($string, "\n");

		if ($eol2 > 0 && $eol2 >= $eol1 || $eol2 >= $eol3) {
			return "\r\n";
		}
		elseif ($eol1 > $eol3) {
			return "\r";
		}
		else {
			return "\n";
		}
	}
}