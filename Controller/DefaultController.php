<?php
/**
 * Created by PhpStorm.
 * User: plazm
 * Date: 4/17/14
 * Time: 10:13 AM
 */

namespace tsCMS\PageBundle\Controller;

use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\HttpKernelInterface;
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

    public function childRenderAction($pageTitle, Request $request)
    {
        /** @var NestedTreeRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository('tsCMSPageBundle:Page');

        $page = $repo->findBy(array("title" => $pageTitle));

        $html = "";
        if ($page) {
            /** @var Page[] $children */
            $children = $repo->children($page, true);

            foreach ($children as $child) {
                $subRequest = $request->duplicate(array(), null, array("_controller" => "tsCMSPageBundle:Default:show", "page" => $child));
                $subRequest->headers->set("X-Requested-With","XMLHttpRequest");
                /** @var Response $response */
                $response = $this->get('http_kernel')->handle($subRequest, HttpKernelInterface::SUB_REQUEST);
                $html .= $response->getContent();
            }
        }
        return new Response($html);
    }
} 