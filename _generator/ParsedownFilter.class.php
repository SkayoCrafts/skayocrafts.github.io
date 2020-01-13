<?php


class ParsedownFilter extends Parsedown {

	private $tagCallback;

	function __construct ($tagCallback) {
		$this->tagCallback = $tagCallback;
	}


	protected function element (array $element) {

		if (isset($this->tagCallback)) {

			if (is_array($element)) {

				if (is_string($element['name'])) {

					$strf = $this->tagCallback;
					$result = $strf($element);

					if ($result === false) {
						//Remove tag.
					}
				}
			}
		}

		//Return result using modified values.
		return parent::element($element);
	}
}