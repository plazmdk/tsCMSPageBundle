<?php

namespace tsCMS\PageBundle\Controller;

use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use JMS\SecurityExtraBundle\Annotation\Secure;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use tsCMS\PageBundle\Entity\Page;
use tsCMS\PageBundle\Form\PageType;
use tsCMS\PageBundle\Services\PageService;
use tsCMS\SystemBundle\Services\RouteService;

/**
 * @Route("/pages")
 */
class PagesController extends Controller
{
    /**
     * @Route("/create")
     * @Secure("ROLE_ADMIN")
     * @Template("tsCMSPageBundle:Pages:page.html.twig")
     */
    public function createAction(Request $request)
    {
        $page = new Page();
        $pageForm = $this->createForm(new PageType(null), $page);
        $pageForm->handleRequest($request);
        if ($pageForm->isValid()) {
            $this->handleParent($page);
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->persist($page);
            $em->flush();

            $this->savePagePath($page);

            return $this->redirect($this->generateUrl("tscms_page_pages_edit",array("id" => $page->getId())));
        }

        return array(
            "page" => null,
            "form" => $pageForm->createView()
        );
    }
    /**
     * @Route("/edit/{id}")
     * @Secure("ROLE_ADMIN")
     * @Template("tsCMSPageBundle:Pages:page.html.twig")
     */
    public function editAction(Request $request, Page $page) {
        $pageForm = $this->createForm(new PageType($page->getId()), $page);
        $pageForm->handleRequest($request);
        if ($pageForm->isValid()) {
            $this->handleParent($page);
            /** @var EntityManager $em */
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            $this->savePagePath($page);

            return $this->redirect($this->generateUrl("tscms_page_pages_edit",array("id" => $page->getId())));
        }
        return array(
            "page" => $page,
            "form" => $pageForm->createView()
        );
    }
    /**
     * @Secure("ROLE_ADMIN")
     * @Route("/delete/{id}")
     */
    public function deleteAction(Page $page) {
        /** @var RouteService $routeService */
        $routeService = $this->get("tsCMS.routeService");
        $name = $routeService->generateNameFromEntity($page);
        $routeService->removeRoute($name);

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->remove($page);
        $em->flush();

        return $this->redirect($this->generateUrl("tscms_system_default_index"));
    }
    /**
     * @Secure("ROLE_ADMIN")
     * @Route("/sortCallback")
     */
    public function sortCallbackAction(Request $request) {
        $sourceId = $request->request->get("source");
        $action = $request->request->get("action");
        $targetId = $request->request->get("target");

        /** @var NestedTreeRepository $repo */
        $repo = $this->getDoctrine()->getManager()->getRepository('tsCMS\PageBundle\Entity\Page');
        /** @var Page $source */
        $source = $repo->find($sourceId);
        /** @var Page $target */
        $target = $repo->find($targetId);

        $nextSiblings = $repo->getNextSiblings($source);
        $targetPosition = array_search($target,$nextSiblings);
        if ($targetPosition !== false) {
            if ($action == "after") {
                $targetPosition++;
            }
            $repo->moveDown($source, $targetPosition);
        } else {
            $previousSiblings = $repo->getPrevSiblings($source);
            $previousSiblings = array_reverse($previousSiblings);
            $targetPosition = array_search($target,$previousSiblings);
            if ($targetPosition !== false) {
                if ($action == "before") {
                    $targetPosition++;
                }
                $repo->moveUp($source,$targetPosition);
            }
        }

        return new Response();
    }

    private function handleParent(Page $page) {
        if (!$page->getParent()) {
            /** @var PageService $pageService */
            $pageService = $this->get("tsCMS_pages.pageservice");
            $page->setParent($pageService->getPagesRoot());
        }
    }


    private function savePagePath(Page $page) {
        /** @var RouteService $routeService */
        $routeService = $this->get("tsCMS.routeService");
        $name = $routeService->generateNameFromEntity($page);
        if ($page->getPath()) {
            $routeService->addRoute($name, $page->getTitle(), $page->getPath(),"tsCMSPageBundle:Default:show","page",array("id" => $page->getId()),array(),false, true);
        } else {
            $routeService->removeRoute($name);
        }
    }
}
