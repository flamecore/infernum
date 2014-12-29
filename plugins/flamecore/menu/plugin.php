<?php
namespace FlameCore\InfernumMenuPlugin;

use FlameCore\Infernum\Extension as BaseExtension;
use FlameCore\Infernum\Application;
use FlameCore\Infernum\Template;
use FlameCore\Infernum\Util;

class Extension extends BaseExtension
{
    public static function run(Application $app)
    {
        $menus = $app->cache('menus', function () use ($app) {
            $menus = array();

            $result = $app['db']->select('menu_types');
            while ($menutype = $result->fetch()) {
                $sql = 'SELECT * FROM `<PREFIX>menu_links` WHERE menutype = ? AND parent = 0 ORDER BY sort_order ASC';
                $menuitems = $app['db']->query($sql, [$menutype['id']])->fetchAll();

                $menus[$menutype['name']] = array(
                    'title' => $menutype['title'],
                    'description' => $menutype['description'],
                    'items' => self::generateMenuTree($menuitems, $app)
                );
            }

            return $menus;
        });

        Template::setGlobal('menus', $menus);
    }

    private static function generateMenuTree(array $menuitems, Application $app)
    {
        $tree = array();

        foreach ($menuitems as $menuitem) {
            $menuitem['external'] = preg_match('#^https?://#', $menuitem['url']);
            $menuitem['url'] = $menuitem['external'] ? $menuitem['url'] : $app->makePageUrl($menuitem['url']);
            $menuitem['selected'] = !$menuitem['external'] ? Util::matchesPatternList($app->getPagePath(), $menuitem['selected_on']) : false;

            $sql = 'SELECT * FROM `<PREFIX>menu_links` WHERE parent = ? ORDER BY sort_order ASC';
            $result = $app['db']->query($sql, [$menuitem['id']]);

            if ($result->numRows() > 0) {
                $menuitem['submenu'] = self::generateMenuTree($result->fetchAll(), $app);
            }

            $tree[] = $menuitem;
        }

        return $tree;
    }
}
