<?php
namespace Watson\Controller;

use Silex\Application;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HomeController {

    /**
     * Home page controller.
     *
     * @param Application $app Silex application
     */
    public function indexAction(Application $app) {
        $links = $app['dao.link']->findAll();
        return $app['twig']->render('index.html.twig', array('links' => $links));
    }

    /**
     * Link details controller.
     *
     * @param integer $id Link id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function linkAction($id, Request $request, Application $app) {
        $link = $app['dao.link']->find($id);
        if ($app['security.authorization_checker']->isGranted('IS_AUTHENTICATED_FULLY')) {
            // A user is fully authenticated : he can add comments
            // Check if he's author for manage link

        }
        return $app['twig']->render('link.html.twig', array(
            'link' => $link
        ));
    }

    /**
     * Links by tag controller.
     *
     * @param integer $id Tag id
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function tagAction($id, Request $request, Application $app) {
        $links = $app['dao.link']->findAllByTag($id);
        $tag   = $app['dao.tag']->findTagName($id);

        return $app['twig']->render('result_tag.html.twig', array(
            'links' => $links,
            'tag'   => $tag
        ));
    }

    /**
     * User login controller.
     *
     * @param Request $request Incoming request
     * @param Application $app Silex application
     */
    public function loginAction(Request $request, Application $app) {
        return $app['twig']->render('login.html.twig', array(
            'error'         => $app['security.last_error']($request),
            'last_username' => $app['session']->get('_security.last_username'),
            )
        );
    }
    public function rssFeedAction(Application $app) {
        $links = $app['dao.link']->findLastFifteen();
    
        $rssFeed = new \SimpleXMLElement('<?xml version="1.0" encoding="UTF-8"?><rss version="2.0" xmlns:atom="http://www.w3.org/2005/Atom"></rss>');
        $channel = $rssFeed->addChild('channel');
        $channel->addChild('title', 'Watson RSS Feed');
        $channel->addChild('link', 'http://localhost:1234/');
        $channel->addChild('description', 'Les derniers liens publiés sur Watson');
    
        $atomLink = $channel->addChild('atom:link', '', 'http://www.w3.org/2005/Atom');
        $atomLink->addAttribute('href', 'http://localhost:1234/rss');
        $atomLink->addAttribute('rel', 'self');
        $atomLink->addAttribute('type', 'application/rss+xml');
    
        foreach ($links as $link) {
            $item = $channel->addChild('item');
            $item->addChild('title', $link->getTitle());
            $item->addChild('link', $link->getUrl());
            $item->addChild('description', $link->getDesc());
            $item->addChild('guid', $link->getUrl());
        }
    
        $response = new Response($rssFeed->asXML(), 200);
        $response->headers->set('Content-Type', 'application/rss+xml; charset=utf-8');
        return $response;
    }
}
