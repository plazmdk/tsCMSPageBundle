<?php
/**
 * Created by PhpStorm.
 * User: plazm
 * Date: 4/16/14
 * Time: 5:04 PM
 */

namespace tsCMS\PageBundle\Services;


use Doctrine\ORM\EntityManager;
use Gedmo\Tree\Entity\Repository\NestedTreeRepository;
use Symfony\Component\Routing\RouterInterface;
use tsCMS\PageBundle\Entity\Page;
use tsCMS\SystemBundle\Event\BuildSiteStructureEvent;
use tsCMS\SystemBundle\Model\SiteStructureAction;
use tsCMS\SystemBundle\Model\SiteStructureGroup;
use tsCMS\SystemBundle\Model\SiteStructureTree;

class PageService {
    /** @var \Doctrine\ORM\EntityManager  */
    private $em;
    /** @var RouterInterface */
    private $router;

    function __construct(EntityManager $em, RouterInterface $router)
    {
        $this->em = $em;
        $this->router = $router;
    }

    /**
     * @return \Doctrine\ORM\EntityManager
     */
    public function getEm()
    {
        return $this->em;
    }

    /**
     * @return \Symfony\Bundle\FrameworkBundle\Routing\Router
     */
    public function getRouter()
    {
        return $this->router;
    }


    public function onBuildSiteStructure(BuildSiteStructureEvent $event) {
        $pagesElement = new SiteStructureGroup("Indholdssider","fa-pencil");
        $pagesElement->addElement(new SiteStructureAction("Opret side",$this->getRouter()->generate("tscms_page_pages_create")));

        $treeRoot = new SiteStructureTree(null,null);
        $treeRoot->setSortCallback($this->getRouter()->generate("tscms_page_pages_sortcallback"));

        /** @var NestedTreeRepository $repo */
        $repo = $this->getEm()->getRepository('tsCMS\PageBundle\Entity\Page');

        $arrayTree = $repo->childrenHierarchy($this->getPagesRoot());
        $this->visitNode($treeRoot, $arrayTree);



        $pagesElement->addElement($treeRoot);
        $event->addElement($pagesElement);
    }

    private function visitNode(SiteStructureTree $node, $roots) {
        foreach ($roots as $root) {
            $newNode = new SiteStructureTree($root['id'],$root['title']);
            $newNode->setAction(new SiteStructureAction("Redigér",$this->getRouter()->generate("tscms_page_pages_edit",array("id" => $root['id']))));

            $this->visitNode($newNode,$root['__children']);
            $node->addSubtreeElement($newNode);
        }
    }

    public function getPagesRoot() {
        /** @var NestedTreeRepository $repo */
        $repo = $this->getEm()->getRepository('tsCMS\PageBundle\Entity\Page');
        $roots = $repo->getRootNodes();
        if (count($roots) == 0) {
            $pageNode = new Page();
            $pageNode->setTitle("Pages Root Node");
            $this->getEm()->persist($pageNode);
            $this->getEm()->flush();
        } else {
            $pageNode = $roots[0];
        }
        return $pageNode;
    }
} 