<?php

namespace Syzygy\DevBundle\Component\Translation;

use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Translation\Translator;

class RegisteringTranslator extends Translator {
	/**
	 * @var LoggerInterface
	 */
	protected $logger;

	public function setLogger(LoggerInterface $logger) {
		$this->logger = $logger;
	}

	protected function loadCatalogue($locale) {
		parent::loadCatalogue($locale);

		$loadedCatalogue = $this->catalogues[$locale];
		if (!$loadedCatalogue instanceof RegisteringMessageCatalogue) {
			$registeringCatalogue = new RegisteringMessageCatalogue($this, $locale);
			$registeringCatalogue->addCatalogue($loadedCatalogue);
			$this->catalogues[$locale] = $registeringCatalogue;
		}
	}

	public function reportMissingTranslation($id, $domain, $locale) {
		if($this->logger) {
			$this->logger->notice(sprintf('Translation missing: `%s` (%s) {%s}', $id, $domain, $locale));
		}
	}

}
