<?php

namespace Syzygy\DevBundle\Component\Translation;

use Psr\Log\LoggerInterface;
use Symfony\Component\Translation\TranslatorInterface;

class RegisteringTranslatorStub implements TranslatorInterface {

	/**
	 * @var TranslatorInterface
	 */
	protected $translator;

	public function __construct(TranslatorInterface $translator, LoggerInterface $logger) {
		$this->translator = $translator;

		if ($translator instanceof RegisteringTranslator) {
			$translator->setLogger($logger);
		}
	}

	// ================ STUBS ================

	public function trans($id, array $parameters = array(), $domain = null, $locale = null) {
		return call_user_func_array(array($this->translator, __FUNCTION__), func_get_args());
	}

	public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null) {
		return call_user_func_array(array($this->translator, __FUNCTION__), func_get_args());
	}

	public function setLocale($locale) {
		return call_user_func_array(array($this->translator, __FUNCTION__), func_get_args());
	}

	public function getLocale() {
		return call_user_func_array(array($this->translator, __FUNCTION__), func_get_args());
	}

}
