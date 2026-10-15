<?php
/**
 * 2019 ZH Media
 *
 * NOTICE OF LICENSE
 *
 * Unauthorized copying of this file, via any medium is strictly prohibited.
 * Do not resell or redistribute this file, either fully or partially.
 * Do not remove this comment containing author information and copyright.
 *
 * @author    Zack Hussain <me@zackhussain.ca>
 * @copyright 2019 ZH Media - All Rights Reserved
 */

namespace CanadaPostWs\Type\Shipment;

class GroupsType
{
    /**
     * @var GroupType[]
     * name="option" type="GroupType" maxOccurs="20"
     */
    protected $groups = array();

    /**
     * @return GroupType[]
     */
    public function getGroups()
    {
        return $this->groups;
    }

    /**
     * @param GroupType[] $groups
     * @return GroupsType
     */
    public function setGroups($groups)
    {
        $this->groups = $groups;

        return $this;
    }

    /**
     * @param GroupType $group
     * @return GroupsType
     */
    public function addGroup($group)
    {
        $this->groups[] = $group;

        return $this;
    }
}
