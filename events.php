<?php

use Concrete\Core\Block\BlockType\BlockType;
use Concrete\Core\Page\Page;

$parentPage =  Page::getByPath('/evenements');

if (is_object($parentPage)) {
    $pageType = \PageType::getByHandle('evenement');
    $template = \PageTemplate::getByHandle('evenement');
    $url = 'sout_adum_6543';
    //champs obligatoires pour page
    $obligatoires_page=array(
        'cName' => 'Thèse Guillaume Blin',
        'cDescription' => 'Ma description d la thèse',
        'cHandle ' => $url,
    );
    $optionnels_page=array('date_fin' => '2023-10-30');
    $page = $parentPage->add($pageType, $obligatoires_page, $template);
    // évènement pas dans le menu
     $page->setAttribute('exclude_nav', true);
     $page->setAttribute('date_debut',  $optionnels_page['date_fin'].' 00:00:00');
     $page->setAttribute('thumbnail', 21);
    $page->setAttribute('lieu',  'Ici ou ailleurs');
    $page->setAttribute('date_fin',  $optionnels_page['date_fin'].' 23:59:59');

    $block = BlockType::getByHandle('html');
    $data = array(
        'content' => '<strong>Juste </strong>un test...'
    );
    $page->addBlock($block, 'Main', $data);
}