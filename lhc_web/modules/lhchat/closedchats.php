<?php

$tpl = erLhcoreClassTemplate::getInstance( 'lhchat/closedchats.tpl.php');


if ( isset($_POST['doDelete']) ) {
	if (!isset($_POST['csfr_token']) || !$currentUser->validateCSFRToken($_POST['csfr_token'])) {
		erLhcoreClassModule::redirect('chat/closedchats');
		exit;
	}
	
	$definition = array(			
			'ChatID' => new ezcInputFormDefinitionElement(
					ezcInputFormDefinitionElement::OPTIONAL, 'int', null, FILTER_REQUIRE_ARRAY
			),
	);
	
	$form = new ezcInputForm( INPUT_POST, $definition );	
	$Errors = array();
	
	if ( $form->hasValidData( 'ChatID' ) ) {
		$chats = erLhcoreClassChat::getList(array('filterin' => array('id' => $form->ChatID)));		
		foreach ($chats as $chatToDelete){
			CSCacheAPC::getMem()->removeFromArray('lhc_open_chats', $chatToDelete->id);		
			if ($currentUser->hasAccessTo('lhchat','deleteglobalchat') || ($currentUser->hasAccessTo('lhchat','deletechat') && $chatToDelete->user_id == $currentUser->getUserID()))
			{
				$chatToDelete->removeThis();
			}			
		}
	}
}

if (isset($_GET['doSearch'])) {
	$filterParams = erLhcoreClassSearchHandler::getParams(array('module' => 'chat','module_file' => 'chat_search','format_filter' => true, 'use_override' => true, 'uparams' => $Params['user_parameters_unordered']));
	$filterParams['is_search'] = true;
} else {
	$filterParams = erLhcoreClassSearchHandler::getParams(array('module' => 'chat','module_file' => 'chat_search','format_filter' => true, 'uparams' => $Params['user_parameters_unordered']));
	$filterParams['is_search'] = false;
}

if ($Params['user_parameters_unordered']['print'] == 1){
	$tpl = erLhcoreClassTemplate::getInstance('lhchat/printchats.tpl.php');
	$items = erLhcoreClassChat::getClosedChats(10000,0,$filterParams['filter']);
	$tpl->set('items',$items);
	$Result['content'] = $tpl->fetch();
	$Result['pagelayout'] = 'popup';
	return;
}

$append = erLhcoreClassSearchHandler::getURLAppendFromInput($filterParams['input_form']);

$pages = new lhPaginator();
$pages->items_total = erLhcoreClassChat::getClosedChatsCount($filterParams['filter']);
$pages->translationContext = 'chat/closedchats';
$pages->serverURL = erLhcoreClassDesign::baseurl('chat/closedchats').$append;
$pages->paginate();
$tpl->set('pages',$pages);

if ($pages->items_total > 0) {
	$items = erLhcoreClassChat::getClosedChats($pages->items_per_page, $pages->low,$filterParams['filter']);
	$tpl->set('items',$items);
}

$filterParams['input_form']->form_action = erLhcoreClassDesign::baseurl('chat/closedchats');
$tpl->set('input',$filterParams['input_form']);
$tpl->set('inputAppend',$append);



$Result['content'] = $tpl->fetch();

$Result['path'] = array(
array('url' =>erLhcoreClassDesign::baseurl('chat/lists'), 'title' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/closedchats','Chats list')),
array('title' => erTranslationClassLhTranslation::getInstance()->getTranslation('chat/closedchats','Closed chats')));


?>