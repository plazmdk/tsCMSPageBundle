<?php
/**
 * Created by PhpStorm.
 * User: plazm
 * Date: 4/17/14
 * Time: 10:13 AM
 */

namespace tsCMS\PageBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use tsCMS\PageBundle\Entity\Page;
use tsCMS\SystemBundle\Services\RouteService;

class DefaultController extends Controller {
    /**
     * @Template()
     */
    public function showAction(Page $page) {
        /** @var RouteService $routeService */
        $routeService = $this->get("tsCMS.routeService");

        if (preg_match_all('#"tscms://([\\\\a-z0-9-_]+)"#i', $page->getContent(),$matches) > 0) {
            $content = $page->getContent();

            foreach ($matches[0] as $index => $match) {
                $route = $routeService->getRouteByName($matches[1][$index]);
                $content = str_replace($match, $route->getPath(),$content);
            }

            $page->setContent($content);
        }


        return array("page" => $page);
    }
} 