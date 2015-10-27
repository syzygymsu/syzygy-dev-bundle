<?php

namespace Syzygy\DevBundle\Component\Translation;

class RegisteringMessageCatalogue extends \Symfony\Component\Translation\MessageCatalogue {
	/**
	 * @var RegisteringTranslator
	 */
	protected $translator;

	public function __construct(RegisteringTranslator $translator, $locale, array $messages = array()) {
		parent::__construct($locale, $messages);
		$this->translator = $translator;
	}

	public function get($id, $domain = 'messages') {
		if (!$this->has($id, $domain)) {
			$this->translator->reportMissingTranslation($id, $domain, $this->getLocale());
		}

		return parent::get($id, $domain);
	}

}
