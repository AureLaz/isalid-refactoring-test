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
        $changes = [];
        //Remplissage des variables
        if ((isset($data['quote']) and $data['quote'] instanceof Quote))
        {
            $quote = $data['quote'];
            $site = SiteRepository::getInstance()->getById($quote->siteId);
            $siteTxt = $site->url . '/' . DestinationRepository::getInstance()->getById($quote->destinationId)->countryName . '/quote/' . $quote->id;
            //Passage par un tableau
            $changes[self::DESTINATION_NAME] = DestinationRepository::getInstance()->getById($quote->destinationId)->countryName;
            $changes[self::QUOTE_SUM_HTML] = Quote::renderHtml($quote);
            $changes[self::QUOTE_SUM] = Quote::renderText($quote);
        }
        $changes[self::DESTINATION_LINK] = $siteTxt;
        $changes[self::USER_FIRSTNAME] = ucfirst(mb_strtolower($user->firstname));
        return $this->replaceText($changes, $text);
    }
    //Remplissage du template avec le tableau rempli précédemment
    private function replaceText(Array $changes, $text)
    {   
        foreach($changes as $key => $value){
            $text = str_replace($key, $value, $text);
        }
        return $text;
    }
}