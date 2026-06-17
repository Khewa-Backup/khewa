<?php
/**
 *  @author    Amazzing <mail@mirindevo.com>
 *  @copyright Amazzing
 *  @license   https://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
 */
if (!defined('_PS_VERSION_')) {
    exit;
}

class MergedValues
{
    public function __construct($af)
    {
        $this->af = $af;
        $this->context = Context::getContext();
    }

    public function extendSQL($action, &$sql)
    {
        switch ($action) {
            case 'install':
                foreach (['attribute', 'feature'] as $type) {
                    $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->qTable($type) . ' (
                        id_merged int(10) unsigned NOT NULL AUTO_INCREMENT,
                        id_group int(10) unsigned NOT NULL,
                        position int(10) unsigned NOT NULL,
                        PRIMARY KEY (id_merged)
                        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
                    $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->qTable($type . '_lang') . ' (
                        id_merged int(10) unsigned NOT NULL,
                        id_lang int(10) unsigned NOT NULL,
                        name text NOT NULL,
                        PRIMARY KEY (id_merged, id_lang), KEY id_lang (id_lang)
                        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
                    $sql[] = 'CREATE TABLE IF NOT EXISTS ' . $this->qTable($type . '_map') . ' (
                        id_original int(10) unsigned NOT NULL,
                        id_merged int(10) unsigned NOT NULL,
                        PRIMARY KEY (id_original, id_merged),
                        KEY id_original (id_original), KEY id_merged (id_merged)
                        ) ENGINE=' . _MYSQL_ENGINE_ . ' DEFAULT CHARSET=utf8';
                }
                break;
            case 'uninstall':
                foreach (['attribute', 'feature'] as $type) {
                    foreach (['', '_lang', '_map'] as $ext) {
                        $sql[] = 'DROP TABLE IF EXISTS ' . $this->qTable($type . $ext);
                    }
                }
                break;
        }
    }

    public function getGeneralSettingsFields()
    {
        return [
            'merged_attributes' => [
                'display_name' => $this->l('Activate merged attributes'),
                'tooltip' => $this->l('For example shoe sizes US-10, UK-9.5 and EUR-43 can be merged in one value'),
                'class' => 'mergedattributes',
                'value' => 0,
                'type' => 'switcher',
                'subtitle' => $this->l('Merged parameters'),
            ],
            'merged_features' => [
                'display_name' => $this->l('Activate merged features'),
                'class' => 'mergedfeatures',
                'value' => 0,
                'type' => 'switcher',
            ],
        ];
    }

    public function assignConfigVariables()
    {
        $smarty_array = [
            'merged_data' => [
                'attribute' => [
                    'title' => $this->l('Merged attributes'),
                    'groups' => $this->af->getGroupOptions('attribute', $this->af->id_lang),
                    'selected_group' => $this->getGroupWithMaxMergedItems('attribute'),
                ],
                'feature' => [
                    'title' => $this->l('Merged features'),
                    'groups' => $this->af->getGroupOptions('feature', $this->af->id_lang),
                    'selected_group' => $this->getGroupWithMaxMergedItems('feature'),
                ],
            ],
        ];
        $this->context->smarty->assign($smarty_array);
    }

    public function getGroupWithMaxMergedItems($type)
    {
        return (int) $this->af->db->getValue('
            SELECT id_group, COUNT(*) as count FROM ' . $this->qTable($type) . '
            GROUP BY id_group ORDER BY count DESC
        ');
    }

    public function renderItems($type, $id_group, $id_lang, $specific_items = false)
    {
        $items = $specific_items ? $specific_items : $this->getItems($type, $id_group);
        $this->context->smarty->assign([
            'items' => $items,
            'item_options' => $this->getOriginalValues($type, $id_group, $id_lang),
            'merging_params' => ['id_group' => $id_group, 'type' => $type],
            'multiple_selection_label' => $this->l('Select values that should be merged'),
        ]);
        $this->af->assignLanguageVariables();

        return $this->af->display($this->af->name, 'views/templates/admin/merged-items.tpl');
    }

    public function getItems($type, $id_group)
    {
        $items = [];
        $data = $this->af->db->executeS('
            SELECT *, main.id_merged FROM ' . $this->qTable($type) . ' main
            LEFT JOIN ' . $this->qTable($type . '_lang') . ' l ON l.id_merged = main.id_merged
            LEFT JOIN ' . $this->qTable($type . '_map') . ' m ON m.id_merged = main.id_merged
            WHERE main.id_group = ' . (int) $id_group . '
            ORDER BY main.position ASC, main.id_merged ASC
        ');
        foreach ($data as $row) {
            $id = $row['id_merged'];
            if (!isset($items[$id])) {
                $items[$id] = [
                    'name' => [$row['id_lang'] => $row['name']],
                    'value' => [$row['id_original'] => $row['id_original']],
                    'position' => $row['position'] + 1, // same format as native attribute positions
                ];
            } else {
                $items[$id]['name'][$row['id_lang']] = $row['name'];
                $items[$id]['value'][$row['id_original']] = $row['id_original'];
            }
        }

        return $items;
    }

    public function getOriginalValues($type, $id_group, $id_lang)
    {
        $values = [];
        $get_values_method = 'get' . Tools::ucfirst($type) . 's';
        foreach ($this->af->$get_values_method($id_lang, $id_group, false) as $v) {
            if (!empty($v['custom'])) {
                $v['name'] .= ' (' . $this->l('custom') . ')';
            }
            $values[$v['id']] = $v['name'];
        }

        return $values;
    }

    public function saveRow($data)
    {
        $sql = $upd_rows = [];
        $id_merged = $data['id_merged'];
        $type = $data['type'];
        $position = $data['position'] - 1; // same format as native attribute positions
        $this->af->db->execute('
            REPLACE INTO ' . $this->qTable($type) . '
            VALUES (' . (int) $id_merged . ', ' . (int) $data['id_group'] . ', ' . (int) $position . ')
        ');
        if (!$id_merged) {
            $id_merged = $this->af->db->Insert_ID();
        }
        foreach ($data['name'] as $id_lang => $name) {
            if (!$name && isset($data['name'][$this->af->id_lang])) {
                $name = $data['name'][$this->af->id_lang];
            }
            $upd_rows['_lang'][] = '(' . (int) $id_merged . ', ' . (int) $id_lang . ', \'' . pSQL($name) . '\')';
        }
        foreach ($data['merged_values'] as $id_original) {
            $upd_rows['_map'][] = '(' . (int) $id_original . ', ' . (int) $id_merged . ')';
        }
        foreach (['_lang', '_map'] as $ext) {
            $sql[] = 'DELETE FROM ' . $this->qTable($type . $ext) . ' WHERE id_merged = ' . (int) $id_merged;
            if (!empty($upd_rows[$ext])) {
                $sql[] = 'INSERT INTO ' . $this->qTable($type . $ext) . ' VALUES ' . implode(', ', $upd_rows[$ext]);
            }
        }
        $this->af->cache('clear', $type[0] . '_list');

        return $this->af->runSQL($sql) ? $id_merged : false;
    }

    public function deleteRow($type, $id_merged)
    {
        $sql = [];
        foreach (['', '_lang', '_map'] as $ext) {
            $sql[] = 'DELETE FROM ' . $this->qTable($type . $ext) . ' WHERE id_merged = ' . (int) $id_merged;
        }

        return $this->af->runSQL($sql) && $this->af->cache('clear', $type[0] . '_list');
    }

    public function mapRows($original_rows, $id_lang, $id_group, $type)
    {
        $updated_rows = $map = [];
        $merged_data = $this->af->db->executeS('
            SELECT * FROM ' . $this->qTable($type) . ' main
            LEFT JOIN ' . $this->qTable($type . '_map') . ' m
                ON m.id_merged = main.id_merged
            LEFT JOIN ' . $this->qTable($type . '_lang') . ' l
                ON l.id_merged = m.id_merged AND l.id_lang = ' . (int) $id_lang . '
            ' . ($id_group ? 'WHERE main.id_group = ' . (int) $id_group : '') . '
        ');
        if ($merged_data) {
            foreach ($merged_data as $merged_row) {
                $map[$merged_row['id_original']]['map' . $merged_row['id_merged']] = $merged_row;
            }
            if ($type == 'attribute' && !empty($original_rows[0]['is_color_group'])) {
                // use colors/textures of merged atts with highest positions
                $original_rows = $this->af->sortByKey($original_rows, 'position');
            }
            foreach ($original_rows as $orig_row) {
                if (isset($map[$orig_row['id']])) {
                    foreach ($map[$orig_row['id']] as $id_merged => $merged_row) {
                        if (!isset($updated_rows[$id_merged])) {
                            $updated_rows[$id_merged] = ['id' => $id_merged] + $merged_row + $orig_row;
                        }
                    }
                } else {
                    $updated_rows[$orig_row['id']] = $orig_row;
                }
            }
            $updated_rows = $this->af->sortByKey($updated_rows, 'name');
        } else {
            $updated_rows = $original_rows;
        }

        return $updated_rows;
    }

    public function mapAttributesInSortedCombinations(&$sorted_combinations)
    {
        $map = $this->getMap('attribute');
        foreach ($sorted_combinations as $id_product => $combinations) {
            foreach ($combinations as $id_comb => $c_data) {
                foreach ($c_data['a'] as $id_group => $id_att) {
                    if (isset($map[$id_att])) {
                        $c_orig = $c_data;
                        $suffix = '';
                        foreach ($map[$id_att] as $id_merged) {
                            $c_orig['a'][$id_group] = $id_merged;
                            $sorted_combinations[$id_product][$id_comb . $suffix] = $c_orig;
                            $suffix .= '_';
                        }
                    }
                }
            }
        }
    }

    public function replaceMergedAttsWithOriginalValues(&$selected_atts)
    {
        $map = $this->getMap('attribute', true);
        foreach ($selected_atts as $id_group => $atts) {
            foreach (array_keys($atts) as $id_att) {
                if (isset($map[$id_att])) {
                    foreach ($map[$id_att] as $id_original) {
                        $selected_atts[$id_group][$id_original] = $id_original;
                    }
                    unset($selected_atts[$id_group][$id_att]);
                }
            }
        }
    }

    public function getMap($type = 'attribute', $reverse = false)
    {
        $map = [];
        $data = $this->af->db->executeS('SELECT * FROM ' . $this->qTable($type . '_map'));
        $keys = !$reverse ? ['id_original', 'id_merged'] : ['id_merged', 'id_original'];
        foreach ($data as $row) {
            $row['id_merged'] = 'map' . $row['id_merged'];
            $map[$row[$keys[0]]][] = $row[$keys[1]];
        }

        return $map;
    }

    public function ajaxAction($action)
    {
        $ret = [];
        switch ($action) {
            case 'getItems':
                $type = Tools::getValue('type');
                $id_group = Tools::getValue('id_group');
                $ret['html'] = $this->renderItems($type, $id_group, $this->af->id_lang);
                break;
            case 'addRow':
                $type = Tools::getValue('type');
                $id_group = Tools::getValue('id_group');
                $position = Tools::getValue('position');
                $items = [0 => ['name' => [], 'value' => [], 'position' => $position]];
                $ret['html'] = $this->renderItems($type, $id_group, $this->af->id_lang, $items);
                break;
            case 'saveRow':
                $data = $this->af->parseStr(Tools::getValue('data'));
                $ret['saved_id'] = $this->saveRow($data);
                break;
            case 'deleteRow':
                $type = Tools::getValue('type');
                $id_merged = Tools::getValue('id_merged');
                $ret['deleted'] = $this->deleteRow($type, $id_merged);
                break;
        }
        exit(json_encode($ret));
    }

    protected function qTable($name)
    {
        return '`' . _DB_PREFIX_ . 'af_merged_' . bqSQL($name) . '`';
    }

    public function l($string)
    {
        return $this->af->l($string, 'MergedValues');
    }
}
