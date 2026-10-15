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

namespace CanadaPostWs\Type\Rating;

class RatingInfoType
{

    /**
     * @var QuoteType[]
     * ref="link" minOccurs="0" maxOccurs="unbounded"
     */
    protected $quotes = array();

    /**
     * @return QuoteType[]
     */
    public function getQuotes()
    {
        return $this->quotes;
    }

    /**
     * @param QuoteType[] $links
     * @return RatingInfoType
     */
    public function setQuote($quotes)
    {
        $this->quotes = $quotes;

        return $this;
    }

    /**
     * @param QuoteType $link
     * @return RatingInfoType
     */
    public function addQuote($quote)
    {
        $this->quotes[] = $quote;

        return $this;
    }
}
