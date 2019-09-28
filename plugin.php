<?php

/**
 * @author    YOOtheme http://www.yootheme.com, adapted by forrestkirby https://github.com/forrestkirby
 * @copyright (C) 2007-2019 YOOtheme GmbH yootheme.com, 2019 forrestkirby github.com/forrestkirby
 * @license   http://www.gnu.org/licenses/gpl.html GNU/GPL
 */

$config = array(

    'name' => 'content/joomla-extended',

    'main' => 'YOOtheme\\Widgetkit\\Content\\Type',

    'config' => array(

        'name'  => 'joomla-extended',
        'label' => 'Joomla Extended',
        'icon'  => 'plugins/content/joomla/content.svg',
        'item'  => array('title', 'content', 'link', 'media'),
        'data'  => array(
            'number'        => 5,
            'offset'        => 0,
            'category'      => '0',
            'subcategories' => '0',
            'featured'      => '',
            'content'       => 'intro',
            'image'         => 'intro',
            'link'          => '',
            'order_by'      => 'ordering',
            'date'          => 'publish_up',
            'author'        => 'author',
            'categories'    => 'categories'
        )

    ),

    'items' => function ($items, $content, $app) {

        $args = array(
            'items'         => $content['number'] ?: 5,
            'catid'         => $content['category'] ? (array) $content['category'] : 0,
            'subcategories' => $content['subcategories'] ?: 0,
            'featured'      => $content['featured'] ? 'only' : '',
            'order'         => $content['order_by'] ?: 'ordering'
        );

        $offset = $content['offset'] ?: 0;

        foreach ($app['joomla.article']->get($args) as $item) {

            if ($offset-- <= 0) {
                $urls   = json_decode($item->urls, true);
                $images = json_decode($item->images);

                $data = array(
                    'title'   => $item->title,
                    'media'   => $images ? ($content['image'] == 'intro' ? $images->image_intro : $images->image_fulltext) : '',
                    'media2'   => $images ? ($content['image'] == 'full' ? $images->image_intro : $images->image_fulltext) : '',
                    'content' => $app['filter']->apply($content['content'] == 'intro' ? $item->introtext : $item->introtext . $item->fulltext, 'content'),
                    'link'    => html_entity_decode($app['joomla.article']->getUrl($item)),
                    'tags'    => array(),
                    'author'  => $content['author'] ? $item->author : '',

                    'categories' => $content['categories'] ? array($item->category_title => \JRoute::_(\ContentHelperRoute::getCategoryRoute($item->catid))) : ''
                );

                if (!empty($content['date'])) {
                    $data['date'] = $content['date'] == 'created' ? $item->created : $item->publish_up;
                }

                if ($content['link'] != '' and $urls and !empty($urls["url{$content['link']}"])) {
                    $data['link'] = html_entity_decode($urls["url{$content['link']}"]);
                }

                // ignore the joomla setting show tags for the filter function
                if (!(isset($item->tags) && $item->tags)) {
                    $item->tags = new JHelperTags;
                    $item->tags->getItemTags('com_content.article', $item->id);
                }

                foreach ($item->tags->itemTags as $tag) {
                   $data['tags'][] = $tag->title;
                }

                $items->add($data);
            }

        }

    },

    'events' => array(

        'init.admin' => function ($event, $app) {
            $app['angular']->addTemplate('joomla-extended.edit', 'plugins/content/joomla-extended/views/edit.php');
        }

    )

);

return defined('_JEXEC') ? $config : false;
