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

namespace CanadaPostWs\Type\Manifest;

class GroupIdListType
{
    /**
     * @var string
     * name="group-id" type="GroupIDType" maxOccurs="unbounded"
     */
    protected $groupId;

    /**
     * @return string
     */
    public function getGroupId()
    {
        return $this->groupId;
    }

    /**
     * @param string $groupId
     * @return GroupIdListType
     */
    public function setGroupId($groupId)
    {
        $this->groupId = $groupId;

        return $this;
    }
}
