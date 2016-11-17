<?php
namespace Grav\Plugin;

use Grav\Common\Plugin;
use RocketTheme\Toolbox\Event\Event;
use Grav\Common\Utils;
use RocketTheme\Toolbox\File\File;
use Symfony\Component\Yaml\Yaml;

/**
 * Class BacklinksPlugin
 * @package Grav\Plugin
 */
class BacklinksPlugin extends Plugin
{
    /**
     * @return array
     *
     * The getSubscribedEvents() gives the core a list of events
     *     that the plugin wants to listen to. The key of each
     *     array section is the event that the plugin listens to
     *     and the value (in the form of an array) contains the
     *     callable (or function) as well as the priority. The
     *     higher the number the higher the priority.
     */
    public static function getSubscribedEvents()
    {
        return [
            'onPluginsInitialized' => ['onPluginsInitialized', 0]
        ];
    }

    /**
     * Initialize the plugin
     */
    public function onPluginsInitialized()
    {
        // Don't proceed if we are in the admin plugin
        if ($this->isAdmin()) {
            return;
        }

        $this->enable([
            'onPagesInitialized' => ['onPagesInitialized', 0],
            'onShutdown' => ['onShutdown', 0]
        ]);
    }

    public function onPagesInitialized() {
        $path = $this->grav['locator']->findResource('user://data', true);
        $path .= DS.static::sanitize($this->grav['config']->get('plugins.backlinks.datafile'));
        $datafh = File::instance($path);
        $data = Yaml::parse($datafh->content());
        $datafh->free();
        $this->grav['twig']->twig_vars['backlinks'] = $data;
    }

    public function onShutdown()
    {
        /** @var Cache $cache */
        $cache = $this->grav['cache'];
        $cache_id = md5('backlink'.$cache->getKey());
        $backlinked = $cache->fetch($cache_id);

        if (!$backlinked) {
            // Commenting the following check out because it caused the whole function 
            // to fail on my system. I'm open to suggestions on how to fix this.

            // check if this function is available, if so use it to stop any timeouts
            // try {
            //     if (!Utils::isFunctionDisabled('set_time_limit') && !ini_get('safe_mode') && function_exists('set_time_limit')) {
            //         set_time_limit(0);
            //     }
            // } catch (\Exception $e) {}

            $backlinks = [];
            /** @var Pages $pages */
            $pages = $this->grav['pages'];
            $uri = $this->grav['uri'];
            $root = $uri->host() . $uri->rootUrl();
            $routes = $pages->routes();

            foreach ($routes as $route => $path) {
                try {
                    $page = $pages->get($path);
                    // get the content and parse it for backlinks
                    $content = $page->rawMarkdown();
                    $matches = array();
                    preg_match_all('/(?<!\!)\[.*?\]\((.*?)[\s\)]/', $content, $matches, PREG_PATTERN_ORDER);
                    foreach ($matches[1] as $link) {
                        // Ignore absolute links and named anchors
                        if ( (!Utils::startsWith($link, 'http://')) && (!Utils::startsWith($link, 'https://')) && (!Utils::startsWith($link, '#')) ) {
                            // resolve to absolute path
                            $abspath = '';
                            if (Utils::startsWith($link, '/')) {
                                $abspath = $link;
                            } else {
                                $abspath = $route.DS.$link;
                            }
                            $abspath = static::resolvePath($abspath);

                            // Record backlink
                            if ($route !== $abspath) {
                                if (array_key_exists($abspath, $backlinks)) {
                                    if (!in_array($route, $backlinks[$abspath])) {
                                        $backlinks[$abspath][] = $route;
                                    }
                                } else {
                                    $backlinks[$abspath] = [$route];
                                }
                            }
                        }
                    }
                } catch (\Exception $e) {
                    // do nothing on error
                }
            }
            $cache->save($cache_id, true);
            $path = $this->grav['locator']->findResource('user://data', true);
            $path .= DS.static::sanitize($this->grav['config']->get('plugins.backlinks.datafile'));
            $datafh = File::instance($path);
            $datafh->lock();
            $datafh->save(YAML::dump($backlinks));
            $datafh->free();
        }
    }

    private static function resolvePath($path) {
        $elements = explode(DS, $path);
        $parents = array();
        foreach ($elements as $dir) {
            switch ($dir) {
                case '.':
                    break;
                case '..':
                    array_pop($parents);
                    break;
                default:
                    $parents[] = $dir;
                    break;
            }
        }
        if (count($parents) === 0) {
            return DS;
        } else {
            return implode(DS, $parents);
        }
    }

    private static function sanitize($fn) {
        $fn = trim($fn);
        $fn = str_replace('..', '', $fn);
        $fn = ltrim($fn, DS);
        $fn = str_replace(DS.DS, DS, $fn);
        return $fn;
    }
}
