<?php
/**
 * plg_q2a_qaintegration
 * @author      Simon Champion, SFW Ltd
 * @copyright   Copyright (C) 2016 SFW Ltd
 * @license     http://www.gnu.org/licenses/gpl-2.0.html GNU/GPL
 * @link        http://www.sfwltd.co.uk/
 */
// no direct access
defined('_JEXEC') or die;

jimport('joomla.plugin.plugin');

/**
 * Question2Answer Integration plugin
 *
 * @package     Joomla
 * @subpackage  Q2a
 * @since       3.4
 */
class PlgQ2aQaintegration extends JPlugin
{
    private static $parentIDs = []; //static so we don't have to keep going back to the DB if we want to check have multiple users from the same group.
    /**
     * Return the access levels available to the user, as defined by the plugin config.
     */
    public function onQnaAccess($user)
    {
        $userViewLevels = JAccess::getAuthorisedViewLevels($user->id);
        $access = $this->getAccessParams();
        return [
            'view' => in_array($access['view'], $userViewLevels),
            'post' => in_array($access['post'], $userViewLevels),
            'edit' => in_array($access['edit'], $userViewLevels),
            'mod'  => in_array($access['mod'], $userViewLevels),
            'admin'=> in_array($access['admin'], $userViewLevels),
            'super'=> in_array($access['super'], $userViewLevels) || $user->get('isRoot'),
        ];
    }

    /**
     * Return the group name (if any) that was responsible for granting the user access to the given view level.
     */
    public function onTeamGroup($user)
    {
        $viewLevel = $this->params->get('view_q2a');
        $db = JFactory::getDbo();
        $query = $db->getQuery(true)
            ->select('rules')
            ->from($db->quoteName('#__viewlevels'))
            ->where('id = '.$db->quote($viewLevel));

        $db->setQuery($query);
        $dbout = $db->loadColumn();
        $rules = json_decode($dbout[0]);
        if (!$rules || !is_array($rules)) { return "Unknown Group"; }

        foreach ($user->groups as $group) {
            if (in_array($group, $rules)) {
                return $this->getGroupNameByID($group);
            }
            $parents = $this->getGroupParents($group);
            foreach ($parents as $parent) {
                if (in_array($parent, $rules)) {
                    return $this->getGroupNameByID($group);
                }
            }
        }
        return "Unknown group";
    }

    private function getGroupParents($groupID)
    {
        $output = [];
        $parentID = $groupID;
        while($parentID) {
            $parentID = $this->getGroupDirectParent($parentID);
            if ($parentID) {
                $output[] = $parentID;
            }
        }
        return $output;
    }
    private function getGroupDirectParent($groupID)
    {
        if (isset(self::$parentIDs[$groupID])) {
            return self::$parentIDs[$groupID];
        }
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select('parent_id')
            ->from($db->quoteName('#__usergroups'))
            ->where('id = '.$db->quote($groupID));
        $db->setQuery($query);
        $dbout = $db->loadColumn();
        self::$parentIDs[$groupID] = $dbout[0];
        return $dbout[0];
    }

    private function getGroupNameByID($groupID)
    {
        $db = JFactory::getDBO();
        $query = $db->getQuery(true)
            ->select('title')
            ->from($db->quoteName('#__usergroups'))
            ->where('id = '.$db->quote($groupID));
        $db->setQuery($query);
        $dbout = $db->loadColumn();
        return $dbout[0];
    }

    private function getAccessParams()
    {
        return [
            'view' => $this->params->get('view_q2a'),
            'edit' => $this->params->get('edit_q2a'),
            'post' => $this->params->get('post_q2a'),
            'mod'  => $this->params->get('mod_q2a'),
            'admin'=> $this->params->get('admin_q2a'),
            'super'=> $this->params->get('super_q2a'),
        ];
    }
}
