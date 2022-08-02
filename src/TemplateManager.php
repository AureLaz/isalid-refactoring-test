<?php

class TemplateManager
{
    //Utilisation de constantes
    CONST DESTINATION_NAME = '[quote:destination_name]';
    CONST DESTINATION_LINK = '[quote:destination_link]';
    CONST QUOTE_SUM_HTML = '[quote:summary_html]';
    CONST QUOTE_SUM = '[quote:summary]';
    CONST USER_FIRSTNAME = '[user:first_name]';

    public function getTemplateComputed(Template $tpl, array $data)
    {
        if (!$tpl) {
            throw new \RuntimeException('no tpl given');
        }

        $replaced = clone($tpl);

        $replaced->subject = $this->computeText($replaced->subject, $data);
        $replaced->content = $this->computeText($replaced->content, $data);

        return $replaced;
    }

    private function computeText($text, array $data)
    {
        $APPLICATION_CONTEXT = ApplicationContext::getInstance();
        //Initialisation des variables
        $quote = null;
        $destination = null;
        $site = null;
        $siteTxt = '';
        $user  = (isset($data['user'])  and ($data['user']  instanceof User))  ? $data['user']  : $APPLICATION_CONTEXT->getCurrentUser();
        //On remplit les variables si nécessaire
        if ((isset($data['quote']) and $data['quote'] instanceof Quote))
        {
            $quote = $data['quote'];
            $site = SiteRepository::getInstance()->getById($quote->siteId);
            $destination = DestinationRepository::getInstance()->getById($quote->destinationId);
            $siteTxt = $site->url . '/' . $destination->countryName . '/quote/' . $quote->id;
        }
        
        return $this->searchAndReplaceText($quote, $destination, $user, $text, $siteTxt);
    }

    private function searchAndReplaceText(?Quote $quote, ?Destination $destination, User $user, $text, $siteTxt)
    {   
        if ($destination)
        {
            (strpos($text, self::DESTINATION_NAME) !== false) and $text = str_replace(self::DESTINATION_NAME,$destination->countryName,$text);

        }
        if ($quote)
        {
            (strpos($text, self::QUOTE_SUM_HTML) !== false) and $text = str_replace(self::QUOTE_SUM_HTML,Quote::renderHtml($quote),$text);
            (strpos($text, self::QUOTE_SUM) !== false) and $text = str_replace(self::QUOTE_SUM,Quote::render($quote),$text);
        }
        (strpos($text, self::DESTINATION_LINK) !== false) and $text = str_replace(self::DESTINATION_LINK,$siteTxt,$text);
        (strpos($text, self::USER_FIRSTNAME) !== false) and $text = str_replace(self::USER_FIRSTNAME, ucfirst(mb_strtolower($user->firstname)), $text);
        
        return $text;
    }
}