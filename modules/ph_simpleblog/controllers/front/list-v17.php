<?php
/**
 * Blog for PrestaShop module by PrestaHome Team.
 *
 * @author    PrestaHome Team <support@prestahome.com>
 * @copyright Copyright (c) 2011-2021 PrestaHome Team - www.PrestaHome.com
 * @license   You only can use module, nothing more!
 */

class PH_SimpleBlogListModuleFrontController extends DefaultListBlogForPrestaShopController
{
    public $context;
    public $sb_category = false;
    public $simpleblog_search;
    public $simpleblog_keyword;
    public $is_search = false;
    public $is_category = false;

    protected $blogCategory;

    protected $listController;

    protected $posts;

    public function init()
    {
        parent::init();

        $sb_category = Tools::getValue('sb_category');
        $module_controller = Tools::getValue('controller');
        $simpleblog_search = Tools::getValue('simpleblog_search');
        $simpleblog_keyword = Tools::getValue('simpleblog_keyword');
        $this->listController = Tools::getValue('controller');

        if ($sb_category) {
            $this->sb_category = $sb_category;
            $this->is_category = true;
        }

        $this->controller_name = 'list';

        if ($this->listController == 'category' && !$this->sb_category) {
            Tools::redirect($this->context->link->getModuleLink('ph_simpleblog', 'list'));
            $this->controller_name = 'category';
        }

        if ($simpleblog_search && $simpleblog_keyword) {
            $this->simpleblog_search = $simpleblog_search;
            $this->simpleblog_keyword = $simpleblog_keyword;
            $this->is_search = true;
        }

        if ($this->sb_category != '') {
            $SimpleBlogCategory = SimpleBlogCategory::getByRewrite($this->sb_category, $this->context->language->id);

            // Category not found so now we are looking for categories in same rewrite but other languages and if we found something, then we redirect 301
            if (!Validate::isLoadedObject($SimpleBlogCategory)) {
                $SimpleBlogCategory = SimpleBlogCategory::getByRewrite($this->sb_category, false);

                if (Validate::isLoadedObject($SimpleBlogCategory)) {
                    $SimpleBlogCategory = new SimpleBlogCategory($SimpleBlogCategory->id, $this->context->language->id);
                    header('HTTP/1.1 301 Moved Permanently');
                    header('Location: ' . SimpleBlogCategory::getLink($SimpleBlogCategory->link_rewrite));
                } else {
                    header('HTTP/1.1 404 Not Found');
                    header('Status: 404 Not Found');
                    Tools::redirect($this->context->link->getPageLink('404'));
                }
            }

            $this->blogCategory = $SimpleBlogCategory;
        }

        $this->context = Context::getContext();

        if (!$this->p) {
            $this->canonicalRedirection();
        }
    }

    public function canonicalRedirection($canonical_url = '')
    {
        if (Validate::isLoadedObject($this->blogCategory)) {
            $this->module->canonicalRedirection($this->blogCategory->url);
        } else {
            $this->module->canonicalRedirection($this->context->link->getModuleLink('ph_simpleblog', 'list'));
        }
    }

    public function getTemplateVarPage()
    {
        $page = parent::getTemplateVarPage();

        if (Validate::isLoadedObject($this->blogCategory)) {
            if (!empty($this->blogCategory->canonical)) {
                $page['canonical'] = $this->blogCategory->canonical;
            } else {
                $page['canonical'] = $this->blogCategory->url;
            }

            $page['body_classes']['blog-for-prestashop-category-'.$this->blogCategory->id] = true;

            return $page;
        } else {
            $page['canonical'] = $this->context->link->getModuleLink($this->module->name, 'list');
        }

        return $page;
    }

    public function initContent()
    {
        $id_lang = $this->context->language->id;

        parent::initContent();

        $blogCategories = [];

        // Category things
        if ($this->sb_category != '') {
            if ($this->blogCategory->id_parent > 0) {
                $parent = new SimpleBlogCategory($this->blogCategory->id_parent, $id_lang);
                $this->context->smarty->assign('parent_category', $parent);
            }

            $finder = new BlogPostsFinder();
            $finder->setIdCategory($this->blogCategory->id);
            $this->posts = $finder->findPosts();

            $this->context->smarty->assign('blogCategory', $this->blogCategory);
            $this->context->smarty->assign('category_rewrite', $this->blogCategory->link_rewrite);
        } elseif ($this->is_search) {
            // @todo: complete refactoring "authors" to 2.0.0
            // Posts by author
            $this->context->smarty->assign('is_search', true);

            // echo SimpleBlogPost::getSearchLink('author', 'kpodemski', $id_lang);
            // @todo: meta titles, blog title, specific layout
            switch ($this->simpleblog_search) {
                case 'author':
                    break;
                case 'tag':
                    break;
            }

            $this->context->smarty->assign('meta_title', $this->l('Posts by', 'list-v17') . ' ' . $this->simpleblog_author . ' - ' . $this->l('Blog', 'list-v17'));

            $this->posts = SimpleBlogPost::findPosts($this->simpleblog_search, $this->simpleblog_keyword, $id_lang, $this->posts_per_page, $this->p);

            $this->context->smarty->assign('pagination', $getTemplateVarPagination());

            $this->context->smarty->assign('posts', $this->posts);
        } else {
            $finder = new BlogPostsFinder();
            $this->posts = $finder->findPosts();
            // if (Tools::getValue('y', 0)) {
            //     // archive
            //     $ids = [];

            //     foreach ($posts as $key => $post) {
            //         $dateAdd = strtotime($post['date_add']);
            //         if (date('Y', $dateAdd) != (int) Tools::getValue('y')) {
            //             unset($posts[$key]);
            //         } else {
            //             $ids[] = $post['id_simpleblog_post'];
            //         }
            //     }

            //     $posts = SimpleBlogPost::getPosts($id_lang, $this->posts_per_page, null, $this->p, true, false, false, null, false, false, null, 'IN', $ids);
            // } else {
            //     $posts = SimpleBlogPost::getPosts($id_lang, $this->posts_per_page, null, $this->p);
            // }
            $blogCategories = SimpleBlogCategory::getCategories();
            $this->context->smarty->assign('blogCategories', $blogCategories);
        }

        $pagination = $this->getTemplateVarPagination();
        $this->context->smarty->assign('pagination', $pagination);

        $this->posts = array_splice($this->posts, $this->p ? ($this->p - 1) * $this->posts_per_page : 0, $this->posts_per_page);
        $this->context->smarty->assign('posts', $this->posts);

        $this->assignMetas();

        if (version_compare(_PS_VERSION_, '1.7', '>=')) {
            $this->setTemplate('module:ph_simpleblog/views/templates/front/1.7/list.tpl');
        } else {
            $this->setTemplate('list.tpl');
        }
    }

    public function getBreadcrumbLinks()
    {
        $breadcrumb = parent::getBreadcrumbLinks();

        $id_lang = $this->context->language->id;

        if (Validate::isLoadedObject($this->blogCategory)) {
            if ($this->blogCategory->id_parent){ 
                $parentCategory = $this->blogCategory->getParent();
                $breadcrumb['links'][] = [
                    'title' => $parentCategory->name,
                    'url' => $parentCategory->link_rewrite,
                ];
            }

            $breadcrumb['links'][] = [
                'title' => $this->blogCategory->name,
                'url' => $this->blogCategory->link_rewrite,
            ];
        }

        return $breadcrumb;
    }

    public function getBlogCategory()
    {
        return $this->blogCategory;
    }
}
