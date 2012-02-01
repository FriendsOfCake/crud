<?php
App::uses('JsHelper', 'View/Helper');

class CrudJsHelper extends JsHelper {

	/**
	 * Starts buffer
	 *
	 * @return void
	 */
	public function bufferStart() {
		ob_start();
	}

	/**
	 * Stops the buffer
	 *
	 * @return void
	 */
	public function bufferStop() {
		$buffer = ob_get_clean();
		$buffer = preg_replace('/(<link[^>]+rel="[^"]*stylesheet"[^>]*>|<img[^>]*>|style="[^"]*")|<script[^>]*>(.*?)<\/script>|<style[^>]*>(.*?)<\/style>|<!--.*?-->/is', '\\1\\2', $buffer);
		$buffer = trim($buffer);
		$this->buffer($buffer);
	}
}