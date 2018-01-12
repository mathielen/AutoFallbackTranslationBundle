<?php

namespace Happyr\AutoFallbackTranslationBundle\Translator;

use Happyr\AutoFallbackTranslationBundle\Service\TranslatorService;
use Symfony\Component\Translation\TranslatorBagInterface;
use Symfony\Component\Translation\TranslatorInterface;

/**
 * @author Tobias Nyholm <tobias.nyholm@gmail.com>
 */
class FallbackTranslator implements TranslatorInterface, TranslatorBagInterface
{
    /**
     * @var TranslatorInterface|TranslatorBagInterface
     */
    private $symfonyTranslator;

    /**
     * @var TranslatorService
     */
    private $translatorService;

    /**
     * @var string
     */
    private $defaultLocale;

    /**
     * @var array
     */
    private $allowedLocales;

    /**
     * @param string $defaultLocale
     * @param TranslatorInterface $symfonyTranslator
     * @param TranslatorService $translatorService
     */
    public function __construct($defaultLocale, array $allowedLocales, TranslatorInterface $symfonyTranslator, TranslatorService $translatorService)
    {
        $this->symfonyTranslator = $symfonyTranslator;
        $this->translatorService = $translatorService;
        $this->defaultLocale = $defaultLocale;
        $this->allowedLocales = $allowedLocales;
    }

    /**
     * {@inheritdoc}
     */
    public function trans($id, array $parameters = array(), $domain = null, $locale = null)
    {
        $id = (string)$id;
        if (!$domain) {
            $domain = 'messages';
        }

        $locale = $locale ? $locale : $this->getLocale();

        $catalogue = $this->getCatalogue($locale);
        if ($catalogue->has($id, $domain)) {
            return $this->symfonyTranslator->trans($id, $parameters, $domain, $locale);
        }

        //hard code the fallback mechanism (from de_DE to de)
        $locale = substr($locale, 0, 2);

        if ($locale === $this->defaultLocale) {
            return $id;
        }
        if (!empty($this->allowedLocales) && !in_array($locale, $this->allowedLocales)) {
            return $id;
        }

        $orgString = $this->symfonyTranslator->trans($id, $parameters, $domain, $this->defaultLocale);

        return $this->translateWithSubstitutedParameters($orgString, $locale, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function transChoice($id, $number, array $parameters = array(), $domain = null, $locale = null)
    {
        $id = (string)$id;
        if (!$domain) {
            $domain = 'messages';
        }

        //hard code the fallback mechanism (from de_DE to de)
        $locale = $locale ? $locale : $this->getLocale();
        $locale = substr($locale, 0, 2);

        $catalogue = $this->getCatalogue($locale);
        if ($catalogue->defines($id, $domain)) {
            return $this->symfonyTranslator->transChoice($id, $number, $parameters, $domain, $locale);
        }

        if ($locale === $this->defaultLocale) {
            // we cant do anything...
            return $id;
        }

        $orgString = $this->symfonyTranslator->transChoice($id, $number, $parameters, $domain, $this->defaultLocale);

        return $this->translateWithSubstitutedParameters($orgString, $locale, $parameters);
    }

    /**
     * {@inheritdoc}
     */
    public function setLocale($locale)
    {
        $this->symfonyTranslator->setLocale($locale);
    }

    /**
     * {@inheritdoc}
     */
    public function getLocale()
    {
        return $this->symfonyTranslator->getLocale();
    }

    /**
     * {@inheritdoc}
     */
    public function getCatalogue($locale = null)
    {
        return $this->symfonyTranslator->getCatalogue($locale);
    }

    /**
     * Passes through all unknown calls onto the translator object.
     */
    public function __call($method, $args)
    {
        return call_user_func_array(array($this->symfonyTranslator, $method), $args);
    }

    /**
     * @param string $orgString This is the string in the default locale. It has the values of $parameters in the string already.
     * @param string $locale you wan to translate to.
     * @param array $parameters
     *
     * @return string
     */
    private function translateWithSubstitutedParameters($orgString, $locale, array $parameters)
    {
        // Replace parameters
        /*$replacements = [];
        foreach ($parameters as $placeholder => $nonTranslatableValue) {
            $replacements[(string) $nonTranslatableValue] = uniqid();
        }

        $replacedString = str_replace(array_keys($replacements), array_values($replacements), $orgString);*/
        $translatedString = $this->getTranslatorService()->translate($orgString, $this->defaultLocale, $locale);

        return str_replace(array_values($parameters), array_keys($parameters), $translatedString);
    }

    /**
     * @return TranslatorService
     */
    protected function getTranslatorService()
    {
        return $this->translatorService;
    }
}
