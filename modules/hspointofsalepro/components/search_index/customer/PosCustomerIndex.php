<?php
/**
 * RockPOS - Point of Sale for PrestaShop.
 *
 * @author    Hamsa Technologies
 * @copyright Hamsa Technologies
 * @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */

/**
 * Customer index for Point of Sale.
 */
class PosCustomerIndex
{

    /**
     * @var int
     */
    public $id_customer;

    /**
     * @var int
     */
    public $id_shop;

    /**
     * @var int
     */
    public $id_lang;

    /**
     * @var array
     *            <pre>
     *            array(
     *            string => PosCustomerWordIndex
     *            ...
     *            )</pre>
     */
    protected $word_indexes = array();

    /**
     * @param int $id_customer
     * @param int $id_shop
     * @param int $id_lang
     */
    public function __construct($id_customer, $id_shop)
    {
        $this->id_customer = (int) $id_customer;
        $this->id_shop = (int) $id_shop;
    }

    /**
     * @param array $words
     *                      <pre>
     *                      array(
     *                      'string',
     *                      'string',
     *                      ...
     *                      )</pre>
     * @param int $weight
     */
    public function addWordIndexes(array $words, $weight)
    {
        if (empty($words)) {
            return;
        }
        if ($weight <= 0) {
            return;
        }
        foreach ($words as $word) {
            $pos_word_index = new PosCustomerWordIndex($word, null, (int) $this->id_shop);
            $pos_word_index->setWeight((int) $weight);
            $pos_word_index->setIdCustomer((int) $this->id_customer);
            $this->addWordIndex($pos_word_index);
        }
    }

    /**
     * @param PosCustomerWordIndex $word_index
     */
    public function addWordIndex(PosCustomerWordIndex $word_index)
    {
        if (isset($this->word_indexes[$word_index->word])) {
            $this->word_indexes[$word_index->word]->addWeight($word_index->getWeight());
        } else {
            $this->word_indexes[$word_index->word] = $word_index;
        }
    }

    public function indexing()
    {
        if (empty($this->word_indexes)) {
            return;
        }
        $word_indexes = array();
        foreach ($this->word_indexes as $word_index) {
            $word_indexes[] = implode(',', array(
                0,
                (int) $this->id_shop,
                '\'' . pSQL($word_index->word) . '\'',
            ));
        }
        Db::getInstance()->execute('INSERT IGNORE INTO `' . _DB_PREFIX_ . 'pos_customer_search_word` (`id_lang`, `id_shop`, `word`) VALUES (' . implode('),(', $word_indexes) . ')');

        $added_words = $this->getAddedWords();
        foreach ($added_words as $record) {
            if (isset($this->word_indexes[$record['word']])) {
                $this->word_indexes[$record['word']]->id_word = (int) $record['id_word'];
            }
        }

        $search_indexes = array();
        foreach ($this->word_indexes as $word_index) {
            $search_indexes[] = implode(',', array(
                $word_index->getIdCustomer(),
                $word_index->id_word,
                $word_index->getWeight(),
            ));
        }
        Db::getInstance()->execute('INSERT INTO `' . _DB_PREFIX_ . 'pos_customer_search_index` (`id_customer`, `id_word`, `weight`) VALUES (' . implode('),(', $search_indexes) . ') ON DUPLICATE KEY UPDATE `weight` = `weight` + VALUES(weight)');
    }

    /**
     * @return array
     *               <pre>
     *               array(
     *               int => array(
     *               'id_word' => int,
     *               'word' => string
     *               ),
     *               ...
     *               )
     */
    protected function getAddedWords()
    {
        $query = new DbQuery();
        $query->select('`id_word`, `word`');
        $query->from('pos_customer_search_word');
        $query->where('`word` IN ("' . implode('","', array_keys($this->word_indexes)) . '")');
        $query->where('`id_lang` = ' . (int) $this->id_lang);
        $query->where('`id_shop` = ' . (int) $this->id_shop);

        return Db::getInstance()->executeS($query);
    }
}
