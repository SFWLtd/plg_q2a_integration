<?php
/*
 * qa-external-users.php
 * @copyright	Copyright (C) 2016 SFW Ltd
 * @license		http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link		http://www.sfwltd.co.uk/
 * @author Simon Champion, SFW Ltd.
 *
 * This code is loaded into Question2Answer to give it single-sign-on functionality with Joomla.
 * Note that this file must be used in conjunction with the Joomla QaIntegration plugin.
 * Please follow the installation instructions provided with the Joomla plugin.
 */

if (!defined('QA_VERSION')) { // don't allow this page to be requested directly from browser
    header('Location: ../');
    exit;
}

function qa_get_mysql_user_column_type()
{
    return "INT";
}

function qa_get_login_links($relative_url_prefix, $redirect_back_to_url)
{
    $config_urls = q2a_GetURLs();
    return array(
        'login'     => $config_urls['login'],
        'register'  => $config_urls['reg'],
        'logout'    => $config_urls['logout']
    );
}

function qa_get_logged_in_user()
{
    qa_get_joomla_app();    // Now we can use all classes of Joomla
    $user = JFactory::getUser();
    $config_urls = q2a_GetURLs();
    
    if($user) {
      if($user->guest || $user->block) {
        header('location:'.$config_urls['denied']);
        die;
      }

      $access = q2a_QnaAccessEvent($user);

      if(!$access['view']) {     //must be in a group that has the view level set at least.
        header('location:'.$config_urls['denied']);
        die;
      }
      $level = QA_USER_LEVEL_BASIC;
      if($access['post'])  {$level = QA_USER_LEVEL_APPROVED;}
      if($access['edit'])  {$level = QA_USER_LEVEL_EDITOR;}
      if($access['mod'])   {$level = QA_USER_LEVEL_MODERATOR;}
      if($access['admin']) {$level = QA_USER_LEVEL_ADMIN;}
      if($access['super'] || $user->get('isRoot')) {$level = QA_USER_LEVEL_SUPER;} 

      $teamGroup = q2a_TeamGroupEvent($user);

      return [
        'userid' => $user->id,
        'publicusername' => "{$user->name} ({$teamGroup})",
        'email' => $user->email,
        'level' => $level,
      ];
    }

    return null;
}

function qa_get_user_email($userid)
{
    qa_get_joomla_app();        // Now you can use all classes of Joomla
    $user = JFactory::getUser();

    if($user) {
      return $user->email;
    }

    return null;
}

function qa_get_userids_from_public($publicusernames)
{
    $output = [];
    if(count($publicusernames)) {
      qa_get_joomla_app();
      foreach($publicusernames as $username) {
        $output[$username] = JUserHelper::getUserId($username);
      }
    }
    return $output;
}

function qa_get_public_from_userids($userids)
{
    $output = [];
    if(count($userids)) {
      qa_get_joomla_app();
      foreach($userids as $userID) {
        $user = JFactory::getUser($userID);
        $teamGroup = q2a_TeamGroupEvent($user);
        $output[$userID] = $user->name." (".$teamGroup.")";
      }
    }
    return $output;
}

function qa_get_logged_in_user_html($logged_in_user, $relative_url_prefix)
{
    $publicusername=$logged_in_user['publicusername'];
    return '<a href="'.qa_path_html('user/'.$publicusername).'" class="qa-user-link">'.htmlspecialchars($publicusername).'</a>';
}

function qa_get_users_html($userids, $should_include_link, $relative_url_prefix)
{
    $useridtopublic=qa_get_public_from_userids($userids);

    $usershtml=array();

    foreach ($userids as $userid) {
        $publicusername=$useridtopublic[$userid];

        $usershtml[$userid]=htmlspecialchars($publicusername);

        if ($should_include_link)
            $usershtml[$userid]='<a href="'.qa_path_html('user/'.$publicusername).'" class="qa-user-link">'.$usershtml[$userid].'</a>';
    }

    return $usershtml;
}

function qa_avatar_html_from_userid($userid, $size, $padding)
{
    return null; // show no avatars by default
}

function qa_user_report_action($userid, $action)
{
    // do nothing by default
}

/**
 * Link to Joomla app. Separate function so we can call it from multiple places if needed.
 */
function qa_get_joomla_app()
{
    define( '_JEXEC', 1 ); //This will define the _JEXEC constant that will allow us to access the rest of the Joomla framework
    //this file should be in joomla-root/plugins/q2a/qaintegration/qa-external/ so track back up the tree to find the Joomla root.
    $joomlaPath = dirname(dirname(dirname(dirname(__DIR__))));
    define('JPATH_BASE', $joomlaPath);
    require_once( JPATH_BASE.'/includes/defines.php' );
    require_once( JPATH_BASE.'/includes/framework.php' );
    // Instantiate the application.
    $app = JFactory::getApplication('site');
    // Initialise the application.
    $app->initialise();
}

function q2a_QnaAccessEvent($user)
{
    return array_pop(q2a_TriggerJoomlaEvent('onQnaAccess', [$user]));
}
function q2a_TeamGroupEvent($user)
{
    return array_pop(q2a_TriggerJoomlaEvent('onTeamGroup', [$user]));
}
function q2a_GetURLs()
{
    return array_pop(q2a_TriggerJoomlaEvent('onGetURLs', []));
}

function q2a_TriggerJoomlaEvent($event, $args)
{
    JPluginHelper::importPlugin('q2a');
    $dispatcher = JEventDispatcher::getInstance();
    $results = $dispatcher->trigger($event, $args);
    return $results;
}