<?php
/*
* Copyright (c) e107 Inc e107.org, Licensed under GNU GPL (http://www.gnu.org/licenses/gpl.txt)
*
* Featurebox shortcode batch class - shortcodes available site-wide. ie. equivalent to multiple .sc files.
*/

if (!defined('e107_INIT')) { exit; }

//e107::lan('eforum');  // English_menu.php or {LANGUAGE}_menu.php

////e107::includeLan(e_PLUGIN.'eforum/languages/'.e_LANGUAGE);


class eforum_shortcodes extends e_shortcode// must match the plugin's folder name. ie. [PLUGIN_FOLDER]_shortcodes
{	
	public $override = true; // when set to true, existing core/plugin shortcodes matching methods below will be overridden. 
	protected $forumObj;
	protected $viewforum_sc;
	protected $tp;
  protected $fv_threadID;
  protected $fv_popts_type;

	function __construct(){
		/*
			parent::__construct();
				$this->e107 = e107::getInstance();
		*/
//    parent::__construct();

			include_once(e_PLUGIN . "forum/forum_class.php");
			$this->forumObj = new e107forum();
		
//			$this->viewforum_sc = e107::getScBatch('view', 'forum');   // Isto não funciona aqui, estoura....
		//    $this->scn = e107::getScBatch('news');
		//    $this->scf = e107::getScBatch('forum');
			$this->tp = e107::getParser();
		//    $this->forumsc = e107::getScBatch('forum',TRUE);
		//    $this->menu['foruminfo'] = e107::getmenu()->isLoaded("foruminfo");
		
		// O breadcrumb passou para o e_parse do eforum
		  }
		
// ############################################
// ##### FORUM PLUGIN OVERRIDE SHORTCODES #####
// ############################################

// Possivelmente pode sair se este pull for aprovado no github e107 https://github.com/e107inc/e107/pull/5415
function sc_post_content($parm = null)
{
	$tp = e107::getParser();
	$pref = e107::getPref();
	$post = strip_tags($tp->toHTML(e107::getScBatch('view', 'forum')->var['post_entry'], true, 'emotes_off, no_make_clickable', '', $pref['menu_wordwrap']));
//		$post = $tp->text_truncate($post, varset($this->param['nfp_characters'], 120), varset($this->param['nfp_postfix'], '...'));
	$post = $tp->truncate($post, varset($this->param['nfp_characters'], (intval($parm['truncate'])??120)), varset($this->param['nfp_postfix'], '...'));

//	var_dump(intval($parm['truncate'])??120);
	return $post;
}

function sc_newflag($parms) // Forum template
{
  $sc = e107::getScBatch('forum');
////////////////////////// MUDAR, já cá tenho o forum sem ser preciso a global..... ainda não...
//  global $forum;
//  var_dump ($forum);
  
//      $forumList = $forum->forumGetForumList();
//-----  $newflag_list = $forum->forumGetUnreadForums();
  $newflag_list = $this->forumObj->forumGetUnreadForums();

//  		$newflag_list = array(1,2,3,4,5);
  //var_dump ($sc->var['forum_replies']);
//  var_dump ($sc->var['forum_id']);
/*
	echo "<pre>";
var_dump ($newflag_list);
  var_dump ($parms);
  echo "</pre>";
*/
  if(USER && is_array($newflag_list) && in_array($sc->var['forum_id'], $newflag_list))
  {
    $url = $sc->sc_lastpost(array('type'=>'url'));
    return ($parms['class']?'forum_newmess':"<a href='".$url."'>".defset('IMAGE_new').'</a>');
  }
  elseif(empty($sc->var['forum_replies']) && defined('IMAGE_noreplies'))
  {
    return ($parms['class']?false:defset('IMAGE_noreplies'));
  }
  return ($parms['class']?false:defset('IMAGE_nonew'));
}

	///
	/// EM TESTE, PARA VER SE VALE A PENA TRAZER O DO TEMA
	///
/*
	function sc_iconkey($template, $force = false)
	{
		return "sdlkfldsfjklsdjfklsdfjaljflksj";
	}
*/
	///
	/// PARA SAIR SE APROVAREM O PULL https://github.com/e107inc/e107/pull/5414
	///
/* ----------------------------
	function sc_quickreply()
	{
//		echo "<hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr>#######################<hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr>";
//
		global $forum, $forum_quickreply, $thread, $FORUM_VIEWTOPIC_TEMPLATE;

		// Define which tinymce4 template should be used, depending if the current user is registered or a guest
		if(!deftrue('e_TINYMCE_TEMPLATE'))
		{
			define('e_TINYMCE_TEMPLATE', (USER ? 'member' : 'public')); // allow images / videos.
		}

//		if($forum->checkPerm($this->var['thread_forum_id'], 'post') && $this->var['thread_active'])
		if($forum->checkPerm($thread->threadInfo['thread_forum_id'], 'post') && $thread->threadInfo['thread_active'])
		{
			//XXX Show only on the last page??
			if(!vartrue($forum_quickreply))
			{
//				$ajaxInsert = ($thread->pages == $thread->page || $thread->pages == 0) ? 1 : 0;
				//	$ajaxInsert = 1;
				//	echo "AJAX-INSERT=".$ajaxInsert ."(".$thread->pages." vs ".$thread->page.")";
//Orphan $frm variable????		$frm = e107::getForm();

				$urlParms = array('f' => 'rp', 'id' => $this->var['thread_id'], 'post' => $this->var['thread_id']);
//				$url = e107::url('forum', 'post', null, array('query' => $urlParms)); // ."?f=rp&amp;id=".$thread->threadInfo['thread_id']."&amp;post=".$thread->threadInfo['thread_id'];

				$vars = array(
					'QR_URL' => e107::url('forum', 'post', null, array('query' => $urlParms)),
					'QR_TOKEN' => e_TOKEN,
					'QR_AJAX' => ($thread->pages == $thread->page || $thread->pages == 0) ? 1 : 0,
					'QR_THID' => $this->var['thread_id'],
					'QR_THFORUMID' => $this->var['thread_forum_id']
				);

				$qr = e107::getPlugPref('forum', 'quickreply', 'default');

				if($qr == 'default')
				{


					$template = e107::getParser()->parseTemplate($FORUM_VIEWTOPIC_TEMPLATE['quickreply'], true, $vars);

				}
				else
				{
					$pref = (array) e107::pref('forum');
					$editor =  varset($pref['editor'], null);
					$editor = is_null($editor) ? 'default' : $editor;

					$textarea = e107::getForm()->bbarea('post', '', 'forum', 'forum', 'medium', array('id' => 'forum-quickreply-text', 'wysiwyg' => $editor));
					$template = preg_replace("~(?s)<textarea.*?</textarea>~", $textarea, $FORUM_VIEWTOPIC_TEMPLATE['quickreply']);
					


//					return $text;
				}

				return e107::getParser()->parseTemplate($template, true, $vars);
				// Preview should be reserved for the full 'Post reply' page. <input type='submit' name='fpreview' value='" . Preview . "' /> &nbsp;
			}
//----	else
//----	{
			return $forum_quickreply;
//----	}
		}
	}
---------------------------------------------*/

// ################ PROPOR ISTO NO GITHUB???????????????????????
// Customizei para os dois tipos (botões em ecras largos e lista em pequenos), até eventualmente posso customizar para tres tipos de ecrans....
// mas vai ser dificil templatizar....
// Provavelmente tenho de propor isto no gihub.....
function sc_postoptions($parms=null)
{
//var_dump ($parms);
//$this->viewforum_sc = $sc = e107::getScBatch('view', 'forum');

//  $tp = e107::getParser();
/*
var_dump($sc->var['thread_id']);
//var_dump($sc->postInfo['post_thread']);
*/
/*
echo "<pre>";
var_dump(BTN_print);
echo "</pre>";
*/
//echo "<pre>";
//var_dump(e107::callMethod('view_shortcodes', 'sc_post_author_name'));
//var_dump(is_object('view_shortcodes'));
//var_dump(class_exists('view_shortcodes'));
//var_dump(get_class('view_shortcodes'));
//var_dump($this);
//	}
//echo "</pre>";
//if($tmp = e107::callMethod('theme_shortcodes', 'sc_breadcrumb', $bread))
//  $threadID = !empty($sc->postInfo['post_thread']) ? $sc->postInfo['post_thread'] : ($sc->var['thread_id']??0);
//  $postID = !empty($sc->postInfo['post_id']) ? $sc->postInfo['post_id'] : 0;
// Estes nunca podem ser 0, senão da´uma therad e um post que nunca vão existir. Se for o caso, não vão gerar nehuum botão....
/*	if (!empty($threadID = $this->viewforum_sc->postInfo['post_thread']??$this->viewforum_sc->var['thread_id']))
	{
		$postID = $this->viewforum_sc->postInfo['post_id']; /// Para sair
		$page= (varset($_GET['p']) ? (int)$_GET['p'] : 1); /// Para sair
  // {EMAILITEM} {PRINTITEM} {REPORTIMG}{EDITIMG}{QUOTEIMG}

//Tenho de meter aqui uma default se for tudo 0, para não mostrar nenhum botão....

  $textbut_start = "<div class='col'><div class='btn-toolbar d-none d-md-flex float-end'>";

  $text_start = '<div class="btn-group pull-right float-right float-end d-md-none">
      <button class="btn btn-default btn-secondary btn-sm btn-small dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">
      ' . LAN_FORUM_8013 . '
        ';
	if(defined('BOOTSTRAP') && BOOTSTRAP !== 4)
	{
    $text_start .= '<span class="caret"></span>';
  }

  $text_start .= '</button><ul class="dropdown-menu pull-right dropdown-menu-end float-right text-right text-end">';
 // $textbut = $text = "";


  if ($parms['what']=='thread' || !$parms['what']) {
  $textbut .= "<div class='btn-group justify-content-center'>";
  $text .= "<li class='text-right text-end float-right'>";
  $textbut .= "<a class='btn btn-default text-nowrap' role='button'" .
  $temptext = " href='" . e_HTTP . "print.php?plugin:forum." . $threadID . "'>" . BTN_print . "</a>";
  $text .="<a class='dropdown-item'" . $temptext ."</li>"; // FIXME

  $text .= "<li class='text-right text-end float-right'>";
  $textbut .= "<a class='btn btn-default text-nowrap' role='button'" .
  // O sc {GLYPH=edit} nem squer dá aqui... tenho de chamar a função...
  $temptext = " href='" . e_HTTP . "email.php?plugin:forum." . $threadID . "'>" . BTN_email . "</a>";
  $text .= "<a class='dropdown-item'" . $temptext ."</li>";
  $textbut .= "</div>";
  }
  
	if (($parms['what']=='post' || !$parms['what']) && $postID) {
    $textbut .= "<div class='btn-group ms-2 justify-content-center'>";

  if(USER) // Report
  {
    $urlReport = e107::url('forum', 'post') . "?f=report&amp;id=" . $threadID . "&amp;post=" . $postID;
    //	$urlReport = $this->e107->url->create('forum/thread/report', "id={$threadID}&post={$postID}");
    $text .= "<li class='text-right text-end float-right'>";
    $textbut .= "<a class='btn btn-warning text-nowrap' role='button'" .
    $temptext = " href='" . $urlReport . "'>" . BTN_report .  "</a>";
    $text .= "<a class='dropdown-item'".$temptext."</li>";
  }

  // Edit
  if((USER && isset($this->viewforum_sc->postInfo['post_user']) && $this->viewforum_sc->postInfo['post_user'] == USERID && $this->viewforum_sc->var['thread_active']))
  {
    //$url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $threadID . "&amp;post=" . $postID;
    $url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $threadID . "&amp;post=" . $postID . "&amp;p=".$page;
    //$url = e107::getUrl()->create('forum/thread/edit', array('id' => $threadID, 'post'=>$postID));
    $text .= "<li class='text-right text-end float-right'>";
    $textbut .= "<a class='btn btn-default text-nowrap' role='button'" .
    $temptext = " href='" . $url . "'>" . BTN_edit . "</a>";
    $text .= "<a class='dropdown-item'".$temptext."</li>";
  }

  // Delete own post, if it is the last in the thread
  if($this->viewforum_sc->thisIsTheLastPost && USER && $this->viewforum_sc->thread->threadInfo['thread_lastuser'] == USERID && !defset('MODERATOR'))
  {
    /* only show delete button when post is not the initial post of the topic
     * AND if this post is the last post in the thread */
/*    if($this->viewforum_sc->var['thread_active'] && empty($this->viewforum_sc->postInfo['thread_start']))
    {
      $text .= "<li class='text-right text-end float-right'>";
      $textbut .= "<a class='btn btn-danger text-nowrap' role='button'" .
      $temptext = " href='" . e_REQUEST_URI . "' data-forum-action='deletepost'  data-confirm='" . LAN_JSCONFIRM . "' data-forum-post='" . $postID . "'>" . BTN_delete . "</a>";
      $text .= "<a class='dropdown-item'".$temptext."</li>";
    }
  }

  if(isset($this->viewforum_sc->postInfo['post_forum']) && $this->forumObj->checkperm($this->viewforum_sc->postInfo['post_forum'], 'post'))
  {
    $url = e107::url('forum', 'post') . "?f=quote&amp;id=" . $threadID . "&amp;post=" . $postID;
    //$url = e107::getUrl()->create('forum/thread/quote', array('id' => $threadID, 'post'=>$postID));
    $text .= "<li class='text-right text-end float-right'>";
    $textbut .= "<a class='btn btn-default text-nowrap' role='button'" .
    $temptext = " href='" . $url . "'>" . BTN_quote . "</a>";
    $text .= "<a class='dropdown-item'".$temptext."</li>";

    //	$text .= "<li class='text-right float-right'><a href='".e107::getUrl()->create('forum/thread/quote', array('id' => $postID))."'>".LAN_FORUM_2041." ".$this->tp->toGlyph('share-alt')."</a></li>";
  }
*/
//  $textbut = ($textbut?$textbut."</div><div class='btn-group me-2 justify-content-center'>":"");
/*
  if(defset('MODERATOR'))
  {
    $textbut .= "</div><div class='btn-group ms-2 justify-content-center'>";
    $text .= "<li role='presentation' class='divider'><hr class='dropdown-divider'></li>";
    $type = ($this->viewforum_sc->postInfo['thread_start']) ? 'thread' : 'Post';

    //	print_a($sc->postInfo);

// Edit
    if((USER && isset($this->viewforum_sc->postInfo['post_user']) && $this->viewforum_sc->postInfo['post_user'] != USERID && $this->viewforum_sc->var['thread_active']))
    {

      //$url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $threadID . "&amp;post=" . $postID;
      $url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $threadID . "&amp;post=" . $postID . "&amp;p=".$page;
      // $url = e107::getUrl()->create('forum/thread/edit', array('id' => $threadID, 'post'=>$postID));

      $text .= "<li class='text-right text-end float-right'>";
      $textbut .= "<a class='btn btn-default text-nowrap' role='button'" .
      $temptext = " href='" . $url . "'>" . BTN_edit . "</a>";
      $text .= "<a class='dropdown-item'" . $temptext . "</li>";
    }

    // only show delete button when post is not the initial post of the topic
    //	if(!$this->forum->threadDetermineInitialPost($postID))
    if(empty($this->viewforum_sc->postInfo['thread_start']))
    {
      $text .= "<li class='text-right text-end float-right'>";
      $textbut .= "<a class='btn btn-danger text-nowrap' role='button'" .
      $temptext = " href='" . e_REQUEST_URI . "' data-forum-action='deletepost' data-confirm='" . LAN_JSCONFIRM . "'  data-forum-post='" . $postID . "'>" . BTN_delete . "</a>";
      $text .= "<a class='dropdown-item'" . $temptext . "</li>";
    }

// Move
    if($type == 'thread')
    {
      $url = e107::url('forum', 'move', array('thread_id' => $threadID));
      $text .= "<li class='text-right text-end float-right'>";
      $textbut .= "<a class='btn btn-default text-nowrap' role='button'" .
      $temptext = " href='" . $url . "'>" . BTN_move . "</a>";
      $text .= "<a class='dropdown-item'" . $temptext . "</li>";
    }
    elseif(e_DEVELOPER === true) //TODO
    {
      $text .= "<li class='text-right text-end float-right'>";
      $textbut .= "<a class='btn btn-default text-nowrap' role='button'" .
      $temptext = " href='" . e107::url('forum', 'split', array('thread_id' => $threadID, 'post_id' => $postID)) . "'>" . BTN_split . "</a>";
      $text .= "<a class='dropdown-item'" . $temptext . "</li>";

    }

    $textbut .= '</div></div>';
  }
*/
//}
/*
  $textbut = ($textbut?$textbut_start.$textbut.'</div>':false);
  $text = ($text_start.$text.'</ul></div>':false);
}
else {
	$textbut = false;
	$text = false;
*/
/*
echo "<pre>";
var_dump ($textbut);
var_dump ($text);
echo "</pre>";
*/
//-----}
//----------------  return ($textbut?$textbut_start.$textbut.'</div>':false).($text?$text_start.$text.'</ul></div>':false);
$this->viewforum_sc = e107::getScBatch('view', 'forum');
//$this->fv_threadID = $this->viewforum_sc->postInfo['post_thread']??$this->viewforum_sc->var['thread_id'];
//var_dump ($this->fv_threadID);

if (!empty($this->fv_threadID = $this->viewforum_sc->postInfo['post_thread']??$this->viewforum_sc->var['thread_id']))
{
	$this->fv_popts_type = (string) $parms['type'];
	//	$postID = $this->viewforum_sc->postInfo['post_id']; /// Para sair
//	$page= (varset($_GET['p']) ? (int)$_GET['p'] : 1); /// Para sair
// {EMAILITEM} {PRINTITEM} {REPORTIMG}{EDITIMG}{QUOTEIMG}

//Tenho de meter aqui uma default se for tudo 0, para não mostrar nenhum botão....

$textbut = "<div class='btn-toolbar d-none d-md-flex float-end'>";

$text = '<div class="btn-group pull-right float-right float-end d-md-none">
  <button class="btn btn-default btn-secondary btn-sm btn-small dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">
  ' . LAN_FORUM_8013 . '
	';
if(defined('BOOTSTRAP') && BOOTSTRAP !== 4)
{
$text .= '<span class="caret"></span>';
}

$text .= '</button><ul class="dropdown-menu pull-right dropdown-menu-end float-right text-right text-end">';

$template = (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));

$viewtopic_template = e107::getTemplate('forum','forum_viewtopic', 'postoptions');
//var_dump($viewtopic_template);

if ($parms['what']=='thread' || !$parms['what']) {
	$template .= $viewtopic_template['thread']??"<div class='btn-group me-2 justify-content-center'>{PO_PRINT_THREAD}{PO_EMAIL_THREAD}</div>";
}
if (($parms['what']=='post' || !$parms['what']) && $this->viewforum_sc->postInfo['post_id']) {
    $template .= $viewtopic_template['post']??"<div class='btn-group ms-2 justify-content-center'>{PO_REPORT_POST}{PO_EDIT_POST}{PO_DELETE_POST}{PO_QUOTE_POST}</div>";
	if(defset('MODERATOR'))
	{
    $buttons = $viewtopic_template['moderator']??"<div class='spt_mod-bg p-1'>Mods:</div>{PO_MEDIT_POST}{PO_MDELETE_POST}{PO_MMOVE_POST}";

    $template .= (!$this->fv_popts_type?"<li role='presentation' class='divider'><hr class='dropdown-divider'></li>{$buttons}":($this->fv_popts_type=="button"?"<div class='btn-group ms-2 justify-content-center'>{$buttons}</div>":null));
	}
}

$template .= (!$this->fv_popts_type?"</ul></div>":($this->fv_popts_type=="button"?"</div>":null));

	return $this->tp->parseTemplate($template);
//return "dkfdlçksdklfksdlkfldksfjklsdfjkklsdfj";
}
}
// Shortcodes novos, talvez possa depois passar para o core com um pull...
function sc_po_print_thread ($parms=null){
//	var_dump ($this->ef_popts_type);
//	var_dump ($this->ef_popts_type=="button");
//	var_dump (!$this->ef_popts_type);
//	$textbut .= "<div class='btn-group me-2 justify-content-center'>";
//var_dump ($this->fv_threadID);
	$text .= "<li class='text-right text-end float-right'>";
	$textbut .= "<a class='btn btn-default text-nowrap {$parms['class']}' role='button'" .
//	$temptext = " href='" . e_HTTP . "print.php?plugin:forum." . $this->fv_threadID . "'>" . BTN_print . "</a>";
	$temptext = " href='" . e_HTTP . "print.php?plugin:forum." . $this->fv_threadID . "'>".($parms['text']??LAN_PRINT_307)."</a>";
	$text .="<a class='dropdown-item'" . $temptext ."</li>"; // FIXME

	return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
}  

function sc_po_email_thread ($parms=null){
	//	var_dump ($this->ef_popts_type);
	//	var_dump ($this->ef_popts_type=="button");
	//	var_dump (!$this->ef_popts_type);
	//	$textbut .= "<div class='btn-group me-2 justify-content-center'>";
//	var_dump ($this->fv_threadID);
	$text .= "<li class='text-right text-end float-right'>";
	$textbut .= "<a class='btn btn-default text-nowrap {$parms['class']}' role='button'" .
	// O sc {GLYPH=edit} nem squer dá aqui... tenho de chamar a função...
	$temptext = " href='" . e_HTTP . "email.php?plugin:forum." . $this->fv_threadID . "'>".($parms['text']??LAN_FORUM_2044)."</a>";
	$text .= "<a class='dropdown-item'" . $temptext ."</li>";
  	
	return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
	}  
	
	function sc_po_report_post ($parms=null){
		if(USER) // Report
	{
//	  $urlReport = e107::url('forum', 'post') . "?f=report&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->fv_postID;
	  //	$urlReport = $this->e107->url->create('forum/thread/report', "id={$threadID}&post={$postID}");
	  $text .= "<li class='text-right text-end float-right'>";
	  $textbut .= "<a class='btn btn-warning text-nowrap {$parms['class']}' role='button'" .
	  $temptext = " href='" . (e107::url('forum', 'post') . "?f=report&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->viewforum_sc->postInfo['post_id']) . "'>".($parms['text']??LAN_FORUM_2046)."</a>";
	  $text .= "<a class='dropdown-item'".$temptext."</li>";
	}
	return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
	}

	function sc_po_edit_post ($parms=null){

	if((USER && isset($this->viewforum_sc->postInfo['post_user']) && $this->viewforum_sc->postInfo['post_user'] == USERID && $this->viewforum_sc->var['thread_active']))
	{
	  //$url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $threadID . "&amp;post=" . $postID;
//	  $url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->viewforum_sc->postInfo['post_id'] . "&amp;p=".(varset($_GET['p']) ? (int)$_GET['p'] : 1);
	  //$url = e107::getUrl()->create('forum/thread/edit', array('id' => $threadID, 'post'=>$postID));
	  $text .= "<li class='text-right text-end float-right'>";
	  $textbut .= "<a class='btn btn-default text-nowrap {$parms['class']}' role='button'" .
	  $temptext = " href='" . e107::url('forum', 'post') . "?f=edit&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->viewforum_sc->postInfo['post_id'] . "&amp;p=".(varset($_GET['p']) ? (int)$_GET['p'] : 1) . "'>".($parms['text']??LAN_EDIT)."</a>";
	  $text .= "<a class='dropdown-item'".$temptext."</li>";
	}
	return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
	}


	function sc_po_medit_post ($parms=null){
		// Edit
		if((USER && isset($this->viewforum_sc->postInfo['post_user']) && $this->viewforum_sc->postInfo['post_user'] != USERID && $this->viewforum_sc->var['thread_active']))
		{
	
		  //$url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $threadID . "&amp;post=" . $postID;
//		  $url = e107::url('forum', 'post') . "?f=edit&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->viewforum_sc->postInfo['post_id']  . "&amp;p=".(varset($_GET['p']) ? (int)$_GET['p'] : 1);
		  // $url = e107::getUrl()->create('forum/thread/edit', array('id' => $threadID, 'post'=>$postID));
	
		  $text .= "<li class='text-right text-end float-right'>";
		  $textbut .= "<a class='btn btn-default text-nowrap spt_mod-btn {$parms['class']}' role='button'" .
		  $temptext = " href='" . e107::url('forum', 'post') . "?f=edit&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->viewforum_sc->postInfo['post_id']  . "&amp;p=".(varset($_GET['p']) ? (int)$_GET['p'] : 1) . "'>".($parms['text']??LAN_EDIT)."</a>";
		  $text .= "<a class='dropdown-item'" . $temptext . "</li>";
		}
		return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
	
		}

	function sc_po_delete_post ($parms=null){
		// Delete own post, if it is the last in the thread
/*
		var_dump($this->viewforum_sc->thisIsTheLastPost);
		var_dump(USER);
		var_dump($this->viewforum_sc->thread->threadInfo['thread_lastuser'] == USERID);
		var_dump(!defset('MODERATOR'));
		var_dump($this->viewforum_sc->thisIsTheLastPost && USER && $this->viewforum_sc->thread->threadInfo['thread_lastuser'] == USERID && !defset('MODERATOR'));
*/
	if($this->viewforum_sc->thisIsTheLastPost && USER && $this->viewforum_sc->thread->threadInfo['thread_lastuser'] == USERID && !defset('MODERATOR'))
	  {
		/* only show delete button when post is not the initial post of the topic
		 * AND if this post is the last post in the thread */
//		var_dump($this->viewforum_sc->var['thread_active'] && empty($this->viewforum_sc->postInfo['thread_start']));
		if($this->viewforum_sc->var['thread_active'] && empty($this->viewforum_sc->postInfo['thread_start']))
		{
		  $text .= "<li class='text-right text-end float-right'>";
		  $textbut .= "<a class='btn btn-danger text-nowrap {$parms['class']}' role='button'" .
		  $temptext = " href='" . e_REQUEST_URI . "' data-forum-action='deletepost'  data-confirm='" . LAN_JSCONFIRM . "' data-forum-post='" . $this->viewforum_sc->postInfo['post_id'] . "'>".($parms['text']??LAN_DELETE)."</a>";
		  $text .= "<a class='dropdown-item'".$temptext."</li>";
		}
	  }
	  return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
	}

	function sc_po_mdelete_post ($parms=null){

    // only show delete button when post is not the initial post of the topic
    //	if(!$this->forum->threadDetermineInitialPost($postID))
    if(empty($this->viewforum_sc->postInfo['thread_start']))
    {
      $text .= "<li class='text-right text-end float-right'>";
//      $textbut .= "<a class='btn btn-danger text-nowrap' role='button'" . $temptext = " href='" . e_REQUEST_URI . "' data-forum-action='deletepost' data-confirm='" . LAN_JSCONFIRM . "'  data-forum-post='" . $this->viewforum_sc->postInfo['post_id']  . "'>" . BTN_delete . "</a>";
      $textbut .= "<a class='btn btn-danger text-nowrap spt_mod-btn {$parms['class']}' role='button'" .
      $temptext = " href='" . e_REQUEST_URI . "' data-forum-action='deletepost' data-confirm='" . LAN_JSCONFIRM . "'  data-forum-post='" . $this->viewforum_sc->postInfo['post_id']  . "'>".($parms['text']??LAN_DELETE)."</a>";//// Botões não funcionam por causa do JS
////      $textbut .= "<button class='btn btn-danger text-nowrap' data-role='button' href='" . e_REQUEST_URI . "' data-forum-action='deletepost' data-confirm='" . LAN_JSCONFIRM . "'  data-forum-post='" . $this->viewforum_sc->postInfo['post_id']  . "'>»»" . BTN_delete . "««</button>";
      $text .= "<a class='dropdown-item' " . $temptext . "</li>";
    }
	return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
	}

	function sc_po_quote_post ($parms=null){
		if(isset($this->viewforum_sc->postInfo['post_forum']) && $this->forumObj->checkperm($this->viewforum_sc->postInfo['post_forum'], 'post'))
	{
//	  $url = e107::url('forum', 'post') . "?f=quote&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->viewforum_sc->postInfo['post_id'];
	  //$url = e107::getUrl()->create('forum/thread/quote', array('id' => $threadID, 'post'=>$postID));
	  $text .= "<li class='text-right text-end float-right'>";
	  $textbut .= "<a class='btn btn-default text-nowrap {$parms['class']}' role='button'" .
	  $temptext = " href='" . e107::url('forum', 'post') . "?f=quote&amp;id=" . $this->fv_threadID . "&amp;post=" . $this->viewforum_sc->postInfo['post_id'] . "'>".($parms['text']??LAN_FORUM_2041)."</a>";
	  $text .= "<a class='dropdown-item'".$temptext."</li>";
  
	  //	$text .= "<li class='text-right float-right'><a href='".e107::getUrl()->create('forum/thread/quote', array('id' => $postID))."'>".LAN_FORUM_2041." ".$this->tp->toGlyph('share-alt')."</a></li>";
	}
	return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
	}

	function sc_po_mmove_post ($parms=null){
		// Move
//		$type = ($this->viewforum_sc->postInfo['thread_start']) ? 'thread' : 'Post';
		
		if ($this->viewforum_sc->postInfo['thread_start'])
//		if($type == 'thread')
{
//  $url = e107::url('forum', 'move', array('thread_id' => $this->fv_threadID));
  $text .= "<li class='text-right text-end float-right'>";
  $textbut .= "<a class='btn btn-default text-nowrap spt_mod-btn {$parms['class_m']}' role='button'" .
  $temptext = " href='" . e107::url('forum', 'move', array('thread_id' => $this->fv_threadID)) . "'>".($parms['text']??LAN_FORUM_2042)."</a>";
  $text .= "<a class='dropdown-item'" . $temptext . "</li>";
}
elseif(e_DEVELOPER === true) //TODO
{
  $text .= "<li class='text-right text-end float-right'>";
  $textbut .= "<a class='btn btn-default text-nowrap spt_mod-btn {$parms['class_s']}' role='button'" .
  $temptext = " href='" . e107::url('forum', 'split', array('thread_id' => $this->fv_threadID, 'post_id' => $this->viewforum_sc->postInfo['post_id'])) . "'>".($parms['text']??LAN_FORUM_2043)."</a>";
  $text .= "<a class='dropdown-item'" . $temptext . "</li>";
}
return (!$this->fv_popts_type?$text:($this->fv_popts_type=="button"?$textbut:null));
}


/// ################## FIM DA PROPOSTA PARA O PLUGIN FORUM











function sc_newthreadbuttonx($parms=null) // Provavelmente tenho de propor isto no codigo do plugin forum....
{
/* ####################Isto é um tema bootstrap, não preciso disto aqui....
  $bootstrap = defined('BOOTSTRAP') ? BOOTSTRAP : false;

  if(!$bootstrap)
  {
    return $this->sc_newthreadbutton();
  }
*/
global $thread;  // Porquê o forum aqui se já o tenho no construct?
//--function newthreadjump($url)
  //--{

////////////////////////// MUDAR, já cá tenho o forum sem ser preciso a global.....
//  global $forum;
////////////////////  $jumpList = $this->forumObj->forumGetAllowed('view');

//  $text = '<div class="btn-group mb-3">';
/*
  $text .=
  ($this->var['ntUrl'] ? '<a href="'.$this->var['ntUrl'].'" class="btn btn-primary">'.LAN_FORUM_1018.'</a>' :'').
      '<button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">
      '.($this->var['ntUrl'] ? '' : LAN_FORUM_1001." ".LAN_FORUM_8013).'<span class="caret"></span>
      <span class="sr-only">Toggle Dropdown</span>
  </button>
      <ul class="dropdown-menu pull-right">
      ';
*/
//  var_dump(e107::getScBatch('viewforum', 'forum')->var);
//-------  $ntUrl=e107::getScBatch('viewforum', 'forum')->var['ntUrl'];
/*
echo "<pre>";
var_dump($thread);
var_dump(e107::getScBatch('viewforum', 'forum')->var['ntUrl']);
//var_dump($this->forumObj);
echo "</pre>";
*/
$ntUrl = (e107::getScBatch('viewforum', 'forum')->var['ntUrl']??e107::url('forum', 'post') . "?f=nt&amp;id=" . $thread->threadInfo['forum_id']);
//var_dump($ntUrl);

//  if ($this->var['ntUrl'])
  if ($ntUrl)
  {
//    $href = $this->var['ntUrl'];
    $href = $ntUrl;
    $disabled = "";
//    $restricted_to_members_only = " data-toggle='tooltip' data-bs-toggle='tooltip' title='".defset(BTNtt_newtopic)."'";
    $restricted_to_members_only = "";
//    $extra_space = "";
  } else {
    $href = "#";
    $disabled = " disabled";
    $restricted_to_members_only = " data-toggle='tooltip' data-bs-toggle='tooltip' title='".LAN_FORUM_0006."'
    style='cursor: not-allowed; pointer-events: all !important;'";
 //   $extra_space = "<span>&nbsp;</span>";
 }
 //var_dump($restricted_to_members_only);

//  $text .='<a href="'.$href.'" class="btn btn-primary'.$disabled.'"'.$restricted_to_members_only.'>'. BTN_newtopic .'</a>';
  $text .='<a href="'.$href.'" class="btn btn-primary '.$parms['class'].$disabled.' "'.$restricted_to_members_only.'>'. ($parms['text']??LAN_FORUM_1018) .'</a>';
////$text .= LAN_FORUM_1017;
/* Isto é um tema bootstrap, não preciso disto aqui....
if($bootstrap !== 4)
  {
      $text .= '<span class="caret"></span>';
    }
*/
/*
  $text .= '<span class="caret"></span>';
  $text .= '
      <span class="sr-only">Toggle Dropdown</span>
  </button>
      <ul class="dropdown-menu pull-right float-right dropdown-menu-end">
      ';
*/  
  //--	foreach($jumpList as $key => $val)
/*
  foreach($jumpList as $val)
  {
    $text .= '<li class="dropdown-item"><a href="' . e107::url('forum', 'forum', $val) . '">' . $val['forum_name'] . '</a></li>';
  }
*/


/*
  $frm = e107::getForm();

  // Remover o replace se aprovarem o pull https://github.com/e107inc/e107/pull/5397 no github E EVENTUALMENTE ACTUALIZAR ISTO NO FORUM DO E107
//  $text .= str_replace('ul class="dropdown-menu"','ul class="dropdown-menu pull-right dropdown-menu-end float-right"',$frm->button('goto',$options,'dropdown',$this->tp->toGlyph('fa-globe') . "&nbsp;". LAN_FORUM_1017,array('class'=>"btn btn-secondary dropdown-toggle")));
  $text .= str_replace('ul class="dropdown-menu"','ul class="dropdown-menu pull-right dropdown-menu-end float-right"',$frm->button('goto',$this->forumnav_ul(),'dropdown',$this->tp->toGlyph('fa-globe') . "&nbsp;". LAN_FORUM_1017,array('class'=>"btn btn-secondary dropdown-toggle")));

*/
/*
  $text .= '
    </ul>
    </div>';
*/
  return $text;

//}
}

function sc_buttonsx($parms = null) // Provavelmente tenho de propor isto no codigo do plugin forum....
{
//  global $thread, $forum;  // Porquê o forum aqui se já o tenho no construct?
  global $thread;  // Porquê o forum aqui se já o tenho no construct?

  if(!is_object($thread))
  {
    e107::getDebug()->log('$thread object is missing');
    return null;
  }

  $url = '';
/*
//  var_dump($this->var['thread_forum_id']);
  echo "<pre>";
//  var_dump($forum);
//  var_dump($thread->var['threadInfo']);
//  var_dump($thread);
  var_dump($thread->threadInfo['forum_id']);
  var_dump($thread->threadInfo['thread_active']);
  echo "</pre>";
//  var_dump($forum->checkPerm($thread->threadInfo['forum_id'], 'post'));
  var_dump($this->forumObj->checkPerm($thread->threadInfo['forum_id'], 'post'));
//  var_dump($this->forumObj->getForumPermList('post'));
//  var_dump($this->var['thread_active']);
*/
//  if($forum->checkPerm($this->var['thread_forum_id'], 'post') && $this->var['thread_active'])
  if($this->forumObj->checkPerm($thread->threadInfo['forum_id'], 'post'))
  {
//    $url = e107::url('forum', 'post') . "?f=rp&amp;id=" . $this->var['thread_id'] . "&amp;post=" . $thread->threadId;
//    $ntUrl = e107::url('forum', 'post') . "?f=nt&amp;id=" . $thread->threadInfo['forum_id'];
    if ($thread->threadInfo['thread_active'])
    {
      $url = e107::url('forum', 'post') . "?f=rp&amp;id=" . $thread->threadInfo['thread_id'] . "&amp;post=" . $thread->threadInfo['thread_id'];
    }
  }

// Se precisar, chamo para aqui o sc_eforum_threadnav
/*
  $text .= "<div class='btn-group me-2' role='group' aria-label='topic buttons'>";
    
  //  }
  
  //	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2001."</a>";
  //	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2002."</a>";
  
  //---- SIMILAR CODE AS SC_NEXTPREV!!!!!!!
  /////////////  $prev = $forum->threadGetNextPrev('prev', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
  /////////////  $next = $forum->threadGetNextPrev('next', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
    $prev = $this->forumObj->threadGetNextPrev('prev', $thread->threadInfo['thread_id'], $thread->threadInfo['forum_id'], $thread->threadInfo['thread_lastpost']);
    $next = $this->forumObj->threadGetNextPrev('next', $thread->threadInfo['thread_id'], $thread->threadInfo['forum_id'], $thread->threadInfo['thread_lastpost']);
  
    if($prev !== false)
    {
  //    $options[] = "<a href='" . e107::url('forum', 'topic', $prev) . "'>" . LAN_FORUM_2001 . "</a>";
  $text .= " <a class='btn btn-default' href='" . e107::url('forum', 'topic', $prev) . "'>" . BTN_left ."</a>";
  }
  
  ///$text .= "<a class='btn btn-primary" . ($ntUrl ? "" : " disabled active") . "'" . ($ntUrl ? "" : " data-toggle='tooltip' data-bs-toggle='tooltip' title='" . BTNtt_newtopic . "' style='cursor: not-allowed; pointer-events: all !important;'") . " href='" . ($ntUrl?:"#") . "'>" . BTN_newtopic . "</a>";
  $text .= $this->sc_track();
  
  if($next !== false)
    {
  //    $options[] = "<a href='" . e107::url('forum', 'topic', $next) . "'>" . LAN_FORUM_2002 . "</a>";
  $text .= "<a class='btn btn-default' href='" . e107::url('forum', 'topic', $next) . "'>" . BTN_right. "</a>";
    }
    $text .= "</div>";
*/    
    //  $tp = e107::getParser();
  $text .= "<a class='btn btn-success me-2 {$parms['class']} ".($url ? "" : " disabled active") . "'" . ($url ? "" : " data-toggle='tooltip' data-bs-toggle='tooltip' title='" .LAN_FORUM_2006."' style='cursor: not-allowed; pointer-events: all !important;'") . " href='" . ($url?:"#") . "'>".($parms['text']??LAN_FORUM_2006)."</a>";

/*
  $text = "<a class='btn btn-primary" . ($url ? "" : " disabled active'") . ($url ? "" : " data-toggle='tooltip' data-bs-toggle='tooltip' title='" . LAN_FORUM_0046 . "'
style='cursor: not-allowed; pointer-events: all !important;'") . " href='" . ($url?:"#") . "'>" . LAN_FORUM_2006 . "</a>" . ($url ? "" : "<span>&nbsp;</span>");
*/
//$replyUrl .= $frm->renderlink('quick_reply', array('link'=>'sef','url'=>$url,'title'=>'click here','class'=>($url ? "" : " data-toggle='tooltip' data-bs-toggle='tooltip' title='" . LAN_FORUM_0046 . "'style='cursor: not-allowed; pointer-events: all !important;'")),"button1");
//$replyUrl .= str_replace("class=",($url ? "" : " data-toggle='tooltip' data-bs-toggle='tooltip' title='" . LAN_FORUM_0046 . "'style='cursor: not-allowed; pointer-events: all !important;'")." class=",$frm->button('quick_reply',LAN_FORUM_2006,null,null,'class=btn-primary '.($url ? '' : ' disabled')));
///$replyUrl .= $frm->button('quick_reply',LAN_FORUM_2006,null,null,'class=btn-primary '.($url ? '' : ' disabled').'/" data-toggle=/"tooltip/" data-bs-toggle=/"tooltip/" title=/"' . LAN_FORUM_0046);

//if($forum->checkPerm($this->var['thread_forum_id'], 'post'))
//if($this->forumObj->checkPerm($thread->threadInfo['forum_id'], 'post'))
//  {
//    $ntUrl = e107::url('forum', 'post') . "?f=nt&amp;id=" . $this->var['thread_forum_id'];
//    $ntUrl = e107::url('forum', 'post') . "?f=nt&amp;id=" . $thread->threadInfo['forum_id'];
//      }
//	$ntUrl = e107::getUrl()->create('forum/thread/new', array('id' => $thread->threadInfo['thread_forum_id']));
///    $options[] = " <a href='" . $ntUrl . "'>" . LAN_FORUM_2005 . "</a>";
/*-----
    $text .= "<div class='btn-group' role='group' aria-label='topic buttons'>";
    
//  }

//	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2001."</a>";
//	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2002."</a>";

//---- SIMILAR CODE AS SC_NEXTPREV!!!!!!!
/////////////  $prev = $forum->threadGetNextPrev('prev', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
/////////////  $next = $forum->threadGetNextPrev('next', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
  $prev = $this->forumObj->threadGetNextPrev('prev', $thread->threadInfo['thread_id'], $thread->threadInfo['forum_id'], $thread->threadInfo['thread_lastpost']);
  $next = $this->forumObj->threadGetNextPrev('next', $thread->threadInfo['thread_id'], $thread->threadInfo['forum_id'], $thread->threadInfo['thread_lastpost']);

  if($prev !== false)
  {
//    $options[] = "<a href='" . e107::url('forum', 'topic', $prev) . "'>" . LAN_FORUM_2001 . "</a>";
$text .= " <a class='btn btn-default' href='" . e107::url('forum', 'topic', $prev) . "'>" . BTN_left ."</a>";
}
-----*/
///$text .= "<a class='btn btn-primary" . ($ntUrl ? "" : " disabled active") . "'" . ($ntUrl ? "" : " data-toggle='tooltip' data-bs-toggle='tooltip' title='" . BTNtt_newtopic . "' style='cursor: not-allowed; pointer-events: all !important;'") . " href='" . ($ntUrl?:"#") . "'>" . BTN_newtopic . "</a>";
//$text .= $this->sc_newthreadbuttonx();
/*-----
if($next !== false)
  {
//    $options[] = "<a href='" . e107::url('forum', 'topic', $next) . "'>" . LAN_FORUM_2002 . "</a>";
$text .= "<a class='btn btn-default' href='" . e107::url('forum', 'topic', $next) . "'>" . BTN_right. "</a>";
  }
$text .= "</div>";
-----*/
//  $text = '<div class="btn-group">
	/**
	 * Generic Button Element. 
	 * @param string $name
	 * @param string|array $value
	 * @param string $action [optional] default is submit - use 'dropdown' for a bootstrap dropdown button. 
	 * @param string $label [optional]
	 * @param string|array $options [optional]
	 * @return string
	 */

/*
$text .= '<div class="btn-group">
     ' . $replyUrl . '
    <button class="btn btn-primary dropdown-toggle" data-toggle="dropdown" data-bs-toggle="dropdown">
    ';
*/
//$text .= $replyUrl;
/*
//Estou a copiar o sc todo só por causa de uma customização aqui.... vvvvvvvvv
$text .= LAN_FORUM_1017;
//Estou a copiar o sc todo só por causa de uma customização aqui.... ^^^^^^^^^

  if(defined('BOOTSTRAP') && BOOTSTRAP !== 4)
  {
    $text .= '<span class="caret"></span>';
  }

  $text .= '
    <span class="sr-only">Toggle Dropdown</span>
    </button>';
*/  
//  $text .= '<ul class="dropdown-menu pull-right dropdown-menu-end float-right"> ';

//// ############################ NÃO SEI O K ISTO FAZ......
/*
foreach($droptions as $key => $val)
  {
    $options[] = '<li>' . $val . '</li>';
  }
*/
//////////////////////////  $options[] = ($options?"<li class='divider'><hr class='dropdown-divider'></li>":null);
//// ############################
/// ##### Também alterei isto aqui..... vvvvvvvvvvvvv
/////$jumpList = $forum->forumGetAllowed();
/*
if(empty($this->permList[$type]))
{
  return array();
}
*/
// isto já está chamado duas vezes, tenho de melhorar isto....
//include(e_PLUGIN.'forum/forum_class.php');










//$forumobj = new e107forum;
/*
$sql = e107::getDb('btnx');
/////////$forumList = implode(',', $this->forumObj->permList['view']);
$forumList = implode(',', $this->forumObj->getForumPermList('view'));
*/
//$forumList = implode(',', $forumObj->permList['view']);
//var_dump ($forumList);
//var_dump ($this->forumObj->getForumPermList('view'));
/*
$qry = "
SELECT forum_id, forum_name, forum_sef FROM `#forum`
WHERE forum_id IN ({$forumList}) AND forum_parent != 0
";
*/
/// Isto pode sair se o pull https://github.com/e107inc/e107/pull/5402 for aprovado....
/*
$qry = "
SELECT f.forum_id, f.forum_name, f.forum_sef, pf.forum_name AS parent_name
FROM `#forum` AS f LEFT JOIN `#forum` AS pf
ON f.forum_parent = pf.forum_id
WHERE f.forum_id IN ({$forumList}) AND f.forum_parent != 0
ORDER BY pf.forum_id, f.forum_name";

//var_dump ($qry);
//$ret = [];
//$jumplist = [];
if ($sql->gen($qry))
{
//  while($row = $sql->fetch())
  while($val = $sql->fetch())
  {
*/
//    $ret[$row['forum_id']] = $row;
//    $jumplist[$row['forum_id']] = $row;
//  }

//}
//return $ret;

/// ##### Fim das alterações ^^^^^^^^
//  foreach($jumpList as $key => $val)
//  {
/*
    echo "<pre>";
    var_dump ($val['parent_name']?$val['parent_name']." - ":"");
*/
    ////    $text .= '<li class="dropdown-item"><a href ="' . e107::url('forum', 'forum', $val) . '">' . $val['forum_name'] . '</a></li>';
//    $droptions[] = '<a href ="' . e107::url('forum', 'forum', $val) . '">' . $val['forum_name'] . '</a>';
/*
    $options[] = '<a href ="' . e107::url('forum', 'forum', $val) . '">' . $val['parent_name'] . " - " . $val['forum_name'] . '</a>';
  }
}
*/



//$options = $this->forumnav_ul();
//array_merge($array1, $array2)
//  $text .= '</ul></div>';
/*
  $frm = e107::getForm();

  // Remover o replace se aprovarem o pull https://github.com/e107inc/e107/pull/5397 no github E EVENTUALMENTE ACTUALIZAR ISTO NO FORUM DO E107
//  $text .= str_replace('ul class="dropdown-menu"','ul class="dropdown-menu pull-right dropdown-menu-end float-right"',$frm->button('goto',$options,'dropdown',$this->tp->toGlyph('fa-globe') . "&nbsp;". LAN_FORUM_1017,array('class'=>"btn btn-secondary dropdown-toggle")));
  $text .= str_replace('ul class="dropdown-menu"','ul class="dropdown-menu pull-right dropdown-menu-end float-right"',$frm->button('goto',array_merge($options,$this->forumnav_ul()),'dropdown',$this->tp->toGlyph('fa-globe') . "&nbsp;". LAN_FORUM_1017,array('class'=>"btn btn-secondary dropdown-toggle")));
*/  
  return $text;
}


/// ####### Mais um botão hardcoded.....
function sc_track($parms=null)
{
  $fpref = (array) e107::pref('forum');

  ////////////////////////// MUDAR, já cá tenho o forum sem ser preciso a global.....
//  global $forum;
//  if(!empty($this->pref['track']) && USER)
  if(!empty($fpref['track']) && USER)
  {
    // BC Fix for old template.
/*
    if(!defined('IMAGE_track'))
    {
      define('IMAGE_track', '<img src="' . img_path('track.png') . '" alt="' . LAN_FORUM_4009 . '" title="' . LAN_FORUM_4009 . '" class="icon S16 action" />');
    }

    if(!defined('IMAGE_untrack'))
    {
      define('IMAGE_untrack', '<img src="' . img_path('untrack.png') . '" alt="' . LAN_FORUM_4010 . '" title="' . LAN_FORUM_4010 . '" class="icon S16 action" />');
    }


    $img = (!empty($this->var['track_userid']) ? defset('IMAGE_track') : defset('IMAGE_untrack'));
*/
$class = (!empty($this->var['track_userid']) ? $parms['classu'] : $parms['classt']);

    /*
      $url = $e107->url->create('forum/thread/view', array('id' => $thread->threadId), 'encode=0'); // encoding could break AJAX call

      $url = e107::url('forum','index');

      $tVars->TRACK .= "
          <span id='forum-track-trigger-container'>
          <a class='btn btn-default btn-sm btn-small e-ajax' data-target='forum-track-trigger' href='{$url}' id='forum-track-trigger'>{$img}</a>
          </span>
          <script>
          e107.runOnLoad(function(){
            $('forum-track-trigger').observe('click', function(e) {
              e.stop();
              new e107Ajax.Updater('forum-track-trigger-container', '{$url}', {
                method: 'post',
                parameters: { //send query parameters here
                  'track_toggle': 1
                },
                overlayPage: $(document.body)
              });
            });
          }, document, true);
          </script>
      ";*/


//      $trackDiz = (varset($this->pref['track'], true)) ? LAN_FORUM_3040 : LAN_FORUM_3041;
      $trackDiz = (varset($fpref['track'], true)) ? LAN_FORUM_3040 : LAN_FORUM_3041;

//	$tVars->TRACK = "<a id='forum-track-button' href='#' title=\"".$trackDiz."\" data-token='".deftrue('e_TOKEN','')."' data-forum-insert='forum-track-button'  data-forum-post='".$thread->threadInfo['thread_forum_id']."' data-forum-thread='".$thread->threadInfo['thread_id']."' data-forum-action='track' name='track' class='e-tip btn btn-default' >".$img."</a>";
    return "<a id='forum-track-button' href='#' title=\"" . $trackDiz . "\" data-token='" . deftrue('e_TOKEN', '') . "' data-forum-insert='forum-track-button'  data-forum-post='" . $this->var['thread_forum_id'] . "' data-forum-thread='" . $this->var['thread_id'] . "' data-forum-action='track' name='track' class='btn btn-default {$class}' >".LAN_FORUM_4009."</a>";

  }

  return '';
}

function sc_poster($parms=null)
{
//$scvf = e107::getScBatch('view', 'forum');
global $thread;  // Porquê o forum aqui se já o tenho no construct?
/*
echo "<hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><hr><pre>";
//var_dump($parms['link']);
var_dump($thread);

//var_dump($sc->getScVars());
echo "</pre>";
*/
//->postInfo
  if(!empty($this->viewforum_sc->postInfo['user_name']))
  {
    $url = e107::getUrl()->create('user/profile/view', array('name' => $this->viewforum_sc->postInfo['user_name'], 'id' => $this->viewforum_sc->postInfo['post_user']));
    return ($parms['name'])?$this->viewforum_sc->postInfo['user_name']:($parms['link']?$url:"<a href='{$url}'>{$this->viewforum_sc->postInfo['user_name']}</a>");
  }
  elseif(!empty($this->viewforum_sc->postInfo['post_user_anon']))
  {
    return '<b>' . e107::getParser()->toHTML($this->viewforum_sc->postInfo['post_user_anon']) . '</b>';
  }

}

// pAssou para o euser, afinal o painel é do euser...
/*--
function sc_usercombo()
{
//  $tp = e107::getParser();
  $sc = e107::getScBatch('view', 'forum');
  //	$text2 = $this->sc_level('special');
  //	$text .= $this->sc_level('pic');
//  $uid = (int) $this->postInfo['post_user'];
//  $uid = (int) $sc->postInfo['post_user'];
//  $ue = $this->tp->parseTemplate("{USER_EXTENDED=location.text_value".$uid."}", true);
//  $username = (empty($this->postInfo['user_name'])) ? LAN_ANONYMOUS : $this->postInfo['user_name'];
$username = (empty($sc->postInfo['user_name'])) ? LAN_ANONYMOUS : $sc->postInfo['user_name'];

//  $userUrl = empty($this->postInfo['post_user']) ? '#' : e107::getUrl()->create('user/profile/view', array('user_id' => $this->postInfo['post_user'], 'user_name' => $username));
//  $userUrl = empty($sc->postInfo['post_user']) ? '#' : e107::getUrl()->create('user/profile/view', array('user_id' => $sc->postInfo['post_user'], 'user_name' => $username));
  // e_HTTP.'user.php?id.'.$this->postInfo['post_user']
//  $text = '<div class="btn-group ">
//  $text = '<a href="' . $userUrl . '">' . $username . '</a>';
  $text = '<a href="' . (empty($sc->postInfo['post_user']) ? '#' : e107::getUrl()->create('user/profile/view', array('user_id' => $sc->postInfo['post_user'], 'user_name' => $username))) . '">' . $username . '</a>';

//  $text .= "<li><a class='dropdown-item' href='#'>" . $this->sc_level('userid') . "</a></li>";
//  $text .= "<li><a class='dropdown-item' href='#'>" . $this->sc_joined() . "</a></li>";
  $text .= "<br>" . $sc->sc_level('userid') ;
//---- Não mostro, não vale a pena  $text .= "<br><small>" . $sc->sc_joined() ."</small>";
//  if($ue)
//  {
//    $text .= "<li><a class='dropdown-item' hre='#'>" . $ue . "</a></li>";
//    $text .= $this->tp->parseTemplate("{USER_EXTENDED=location.text_value".(int) $sc->postInfo['post_user']."}", true)??"";
    $text .= "{USER_EXTENDED=location.text_value".(int) $sc->postInfo['post_user']."}";
---*/
//  }
//  $text .= "<li><a class='dropdown-item' href='#'>" . $this->sc_posts() . "</a></li>";
//  $text .= "<a href='#'>" . $sc->sc_posts() . "</a>";
//  $text .= $this->sc_eforum_postsuser();

//  if(e107::isInstalled('pm') && ($this->postInfo['post_user'] > 0))
/*
  if(e107::isInstalled('pm') && ($sc->postInfo['post_user'] > 0))
  {
//    if($pmButton = $this->tp->parseTemplate("{SENDPM: user=" . $this->postInfo['post_user'] . "&glyph=envelope&class=pm-send}", true))
//    if($pmButton = $this->tp->parseTemplate("{SENDPM: user=" . $sc->postInfo['post_user'] . "&glyph=envelope&class=btn pm-send}", true))
//    {
//      $text .= "<li class='divider'><hr class='dropdown-divider'></li>";
//      $text .= "<li class='dropdown-item'>" . $pmButton . "</li>";
        $text .= "{SENDPM: user=" . $sc->postInfo['post_user'] . "&glyph=envelope&class=btn pm-send}";
//    }

    // $text .= "<li><a href='".e_PLUGIN_ABS."pm/pm.php?send.{$this->postInfo['post_user']}'>".$tp->toGlyph('envelope')." ".LAN_FORUM_2036." </a></li>";
  }
*/
/*--
//  $text .= $this->sc_eforum_pmuser();

//  if($website = $this->sc_website())
  if($website = $sc->sc_website())
  {
//    $text .= "<li class='dropdown-item'>" . $website . "</li>";
    $text .= $website ;
  }

//	{EMAILIMG}
//	{WEBSITEIMG}

//  $text .= "</ul></div>";
  return $this->tp->parseTemplate($text);
}
--*/
//************ CUSTOM FORUM SHORTCODES FOR USERCOMBO ***************/
///////NÃO USO NO USER COMBO
/*
function sc_eforum_pmuser()
{
  $sc = e107::getScBatch('view', 'forum');

  if(e107::isInstalled('pm') && ($sc->postInfo['post_user'] > 0))
  {
//    if($pmButton = $this->tp->parseTemplate("{SENDPM: user=" . $this->postInfo['post_user'] . "&glyph=envelope&class=pm-send}", true))
//    if($pmButton = $this->tp->parseTemplate("{SENDPM: user=" . $sc->postInfo['post_user'] . "&glyph=envelope&class=btn pm-send}", true))
//    {
//      $text .= "<li class='divider'><hr class='dropdown-divider'></li>";
//      $text .= "<li class='dropdown-item'>" . $pmButton . "</li>";
       return $this->tp->parseTemplate("{SENDPM: user=" . $sc->postInfo['post_user'] . "&glyph=envelope}");
//    }

    // $text .= "<li><a href='".e_PLUGIN_ABS."pm/pm.php?send.{$this->postInfo['post_user']}'>".$tp->toGlyph('envelope')." ".LAN_FORUM_2036." </a></li>";
  }
}
*/
/*
function sc_eforum_user_posts($parms = null)
{
  $sc = e107::getScBatch('view', 'forum');
/////var_dump ($this->postinfo);
  return (int) $sc->postInfo['user_plugin_forum_posts'];
}
*/

// ########## END OF REWRITEN ORIGINAL FORUM SHORTCODES ##############
// ####################################
// ##### PLUGIN GLOBAL SHORTCODES #####
// ####################################

//Para templatizar depois....
function sc_eforum_threadnav($parms=null)
{

  global $thread;  // Porquê o forum aqui se já o tenho no construct?

  if(!is_object($thread))
  {
    e107::getDebug()->log('$thread object is missing');
    return null;
  }

$text = "<div class='btn-group me-2' role='group' aria-label='topic buttons'>";
    
//  }

//	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2001."</a>";
//	$options[] = "<a href='" . e107::getUrl()->create('forum/thread/prev', array('id' => $thread->threadId)) . "'>".LAN_FORUM_1017." ".LAN_FORUM_2002."</a>";

//---- SIMILAR CODE AS SC_NEXTPREV!!!!!!!
/////////////  $prev = $forum->threadGetNextPrev('prev', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
/////////////  $next = $forum->threadGetNextPrev('next', $thread->threadId, $this->var['forum_id'], $this->var['thread_lastpost']);
  $prev = $this->forumObj->threadGetNextPrev('prev', $thread->threadInfo['thread_id'], $thread->threadInfo['forum_id'], $thread->threadInfo['thread_lastpost']);
  $next = $this->forumObj->threadGetNextPrev('next', $thread->threadInfo['thread_id'], $thread->threadInfo['forum_id'], $thread->threadInfo['thread_lastpost']);

  if($prev !== false)
  {
//    $options[] = "<a href='" . e107::url('forum', 'topic', $prev) . "'>" . LAN_FORUM_2001 . "</a>";
$text .= " <a class='btn btn-default {$parms['classl']}' href='" . e107::url('forum', 'topic', $prev) . "'>".($parms['textl']??LAN_FORUM_2001)."</a>";
}

///$text .= "<a class='btn btn-primary" . ($ntUrl ? "" : " disabled active") . "'" . ($ntUrl ? "" : " data-toggle='tooltip' data-bs-toggle='tooltip' title='" . BTNtt_newtopic . "' style='cursor: not-allowed; pointer-events: all !important;'") . " href='" . ($ntUrl?:"#") . "'>" . BTN_newtopic . "</a>";
$text .= $this->sc_track(array('classt'=>$parms['classt'],'textt'=>$parms['textt'],'classu'=>$parms['classu'],'textu'=>$parms['textu']));

if($next !== false)
  {
//    $options[] = "<a href='" . e107::url('forum', 'topic', $next) . "'>" . LAN_FORUM_2002 . "</a>";
$text .= "<a class='btn btn-default {$parms['classr']}' href='" . e107::url('forum', 'topic', $next) . "'>" . ($parms['textr']??LAN_FORUM_2001). "</a>";
  }
  $text .= "</div>";



  return $text;
}

}