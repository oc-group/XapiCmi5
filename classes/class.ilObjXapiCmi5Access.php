<?php
/**
 * Copyright (c) 2018 internetlehrer-gmbh.de
 * GPLv2, see LICENSE 
 */

include_once('./Services/Repository/classes/class.ilObjectPluginAccess.php');

/**
 * xApi plugin: object acccess check
 *
 * @author Uwe Kohnle <kohnle@internetlehrer-gmbh.de>
 * @version $Id$
 */ 
class ilObjXapiCmi5Access extends ilObjectPluginAccess
{
	const ACTIVATION_OFFLINE = 0;
	const ACTIVATION_UNLIMITED = 1;
	
	private static $settings_cache = array();

	/**
	* checks wether a user may invoke a command or not
	* (this method is called by ilAccessHandler::checkAccess)
	*
	* @param	string		$a_cmd		command (not permission!)
	* @param	string		$a_permission	permission
	* @param	int			$a_ref_id	reference id
	* @param	int			$a_obj_id	object id
	* @param	int			$a_user_id	user id (if not provided, current user is taken)
	*
	* @return	boolean		true, if everything is ok
	*/
	function _checkAccess($a_cmd, $a_permission, $a_ref_id, $a_obj_id, $a_user_id = "")
	{
		global $ilUser, $lng, $rbacsystem, $ilAccess;


		if ($a_user_id == "")
		{
			$a_user_id = $ilUser->getId();
		}

		switch ($a_permission)
		{
			case "visible":
			case "read":
				if (!self::_lookupOnline($a_obj_id) &&
					(!$rbacsystem->checkAccessOfUser($a_user_id,'write', $a_ref_id)))
				{
					return false;
				}
				break;
		}

		return true;
	}

	/*
	* check wether content is online
	*/
	static function _lookupOnline($a_obj_id)
	{
		$row = self::fetchSettings($a_obj_id);
 
		switch($row["availability_type"])
		{
			case self::ACTIVATION_UNLIMITED:
				return true;

			case self::ACTIVATION_OFFLINE:
				return false;

			default:
				return false;
		}
	}

	
	/**
	* get the type
	*/
	static function _lookupTypeId($a_obj_id)
	{
		$row = self::fetchSettings($a_obj_id);
        return $row['type_id'];
	}
	
	
	/**
	 * fetch the settings of an object that are needed for list views
	 * (the settings are cached)
	 * 
	 * @param 	integer		object id
	 * @return	array		fetched row
	 */
	private static function fetchSettings($a_obj_id)
	{
		if (!is_array(self::$settings_cache[$a_obj_id]))
		{
	       	global $ilDB;
	       	
	       	// include only the colums neccessary for object listings
	        $query = 'SELECT type_id, availability_type '
	        		. ' FROM xxcf_data_settings '
	        		. ' WHERE obj_id = ' . $ilDB->quote($a_obj_id, 'integer');
	        		
	        $result = $ilDB->query($query);
	        if (!$row = $ilDB->fetchAssoc($result))
	        {
	        	$row = array();
	        }
	        self::$settings_cache[$a_obj_id] = $row;
		}
		
		return self::$settings_cache[$a_obj_id];
    }
    
    public static function hasOutcomesAccess($object) {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $outcomesAccess = $DIC->access()->checkAccess(
            'read_outcomes',
            '',
            $object->getRefId(),
            $object->getType(),
            $object->getId()
        );
        if ($outcomesAccess) {
            return true;
        }
        return false;
    }

    public static function hasDeleteXapiDataAccess($object) {
        global $DIC; /* @var \ILIAS\DI\Container $DIC */
        $deleteAccess = $DIC->access()->checkAccess(
            'delete_xapi_data',
            '',
            $object->getRefId(),
            $object->getType(),
            $object->getId()
        );
        if ($deleteAccess) {
            return true;
        }
        return false;
    }
}

?>
