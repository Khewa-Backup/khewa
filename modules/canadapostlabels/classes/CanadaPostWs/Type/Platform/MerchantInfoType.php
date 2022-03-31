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

namespace CanadaPostWs\Type\Platform;

class MerchantInfoType
{
    /**
     * @var string
     */
    protected $customerNumber;
    /**
     * @var string
     */
    protected $contractNumber;
    /**
     * @var string
     */
    protected $merchantUsername;
    /**
     * @var string
     */
    protected $merchantPassword;
    /**
     * @var string
     */
    protected $hasDefaultCreditCard;
    /**
     * @var string
     */
    protected $hasDefaultSupplierAccount;

    /**
     * @return string
     */
    public function getCustomerNumber()
    {
        return $this->customerNumber;
    }

    /**
     * @param string $customerNumber
     */
    public function setCustomerNumber($customerNumber)
    {
        $this->customerNumber = $customerNumber;
    }

    /**
     * @return string
     */
    public function getContractNumber()
    {
        return $this->contractNumber;
    }

    /**
     * @param string $contractNumber
     */
    public function setContractNumber($contractNumber)
    {
        $this->contractNumber = $contractNumber;
    }

    /**
     * @return string
     */
    public function getMerchantUsername()
    {
        return $this->merchantUsername;
    }

    /**
     * @param string $merchantUsername
     */
    public function setMerchantUsername($merchantUsername)
    {
        $this->merchantUsername = $merchantUsername;
    }

    /**
     * @return string
     */
    public function getMerchantPassword()
    {
        return $this->merchantPassword;
    }

    /**
     * @param string $merchantPassword
     */
    public function setMerchantPassword($merchantPassword)
    {
        $this->merchantPassword = $merchantPassword;
    }

    /**
     * @return string
     */
    public function getHasDefaultCreditCard()
    {
        return $this->hasDefaultCreditCard;
    }

    /**
     * @param string $hasDefaultCreditCard
     */
    public function setHasDefaultCreditCard($hasDefaultCreditCard)
    {
        $this->hasDefaultCreditCard = $hasDefaultCreditCard;
    }

    /**
     * @return string
     */
    public function getHasDefaultSupplierAccount()
    {
        return $this->hasDefaultSupplierAccount;
    }

    /**
     * @param string $hasDefaultSupplierAccount
     */
    public function setHasDefaultSupplierAccount($hasDefaultSupplierAccount)
    {
        $this->hasDefaultSupplierAccount = $hasDefaultSupplierAccount;
    }
}
