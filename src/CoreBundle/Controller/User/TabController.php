<?php

namespace CoreBundle\Controller\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use CoreBundle\Form\Type\TabType;
use CoreBundle\Entity\Tab;
use CoreBundle\Controller\BaseController;

class TabController extends BaseController
{

    /**
     * Get all tabs for a user
     * @ApiDoc(
     *  description="Get all tabs for a user",
     *  section="3-Tabs",
     *  output={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"tab"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"tab"})
     * @Rest\Get("/users/{user_id}/tabs")
     */
    public function getTabsAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:User')
                ->find($request->get('user_id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        return $user->getTabs();
    }

    /**
     * Get a tab by id
     * @ApiDoc(
     *  description="Get a tab by id",
     *  section="3-Tabs",
     *  output={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"tab"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"tab"})
     * @Rest\Get("/users/{user_id}/tabs/{tab_id}")
     */
    public function getTabAction(Request $request)
    {
        $tab = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:Tab')
                ->find($request->get('tab_id'));

        if (empty($tab)) {
            return $this->tabNotFound();
        }
        if ($request->get('user_id') != $tab->getUser()->getId()) {
            return $this->userNotCorrect();
        }

        return $tab;
    }

    /**
     * Add tab(s) for a user
     * @ApiDoc(
     *  description="Add tab(s) for a user",
     *  section="3-Tabs",
     *  input={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"post"}
     *  },
     *  output={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"tab"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"tab"})
     * @Rest\Post("/users/{user_id}/tabs")
     */
    public function postTabsAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:User')
                ->find($request->get('user_id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        $tab = new Tab();
        $tab->setUser($user);
        $form = $this->createForm(TabType::class, $tab);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            foreach ($tab->getAchievements() as $achievement) {
                $achievement->setTab($tab);
            }
            $tab->defaultValues(true);
            $em->persist($tab);
            $em->flush();
            $this->checkOrderNumber(0, $tab->getOrderNumber(), $tab->getUser()->getId());
            $em->flush();
            return $tab;
        } else {
            return $form;
        }
    }

    /**
     * Remove a tab by id for a user
     * @ApiDoc(
     *  description="Remove a tab by id for a user",
     *  section="3-Tabs"
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{user_id}/tabs/{tab_id}")
     */
    public function deleteTabAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $tab = $em->getRepository('CoreBundle:Tab')
                ->find($request->get('tab_id'));

        if ($tab) {
            if ($tab->getUser()->getId() != $request->get('user_id')) {
                $this->userNotCorrect();
            }
            $em->remove($tab);
            $em->flush();
        }
    }

    /**
     * Complete update of a tab of a user
     * @ApiDoc(
     *  description="Complete update of a tab of a user",
     *  section="3-Tabs",
     *  input={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"tab"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"tab"})
     * @Rest\Put("/users/{user_id}/tabs/{tab_id}")
     */
    public function updateTabAction(Request $request)
    {
        return $this->updateTab($request, true);
    }

    /**
     * Partial update of a tab of a user
     * @ApiDoc(
     *  description="Partial update of a tab of a user",
     *  section="3-Tabs",
     *  input={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\Tab",
     *      "groups"={"tab"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"tab"})
     * @Rest\Patch("/users/{user_id}/tabs/{tab_id}")
     */
    public function patchTabAction(Request $request)
    {
        return $this->updateTab($request, false);
    }

    private function updateTab(Request $request, $clearMissing)
    {
        $tab = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:Tab')
                ->find($request->get('tab_id'));

        if (empty($tab)) {
            return $this->tabNotFound();
        }

        if ($tab->getUser()->getId() != $request->get('user_id')) {
            return $this->userNotCorrect();
        }

        $oldOrderNumber = $tab->getOrderNumber();
        $form = $this->createForm(TabType::class, $tab);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $tab->defaultValues($oldOrderNumber != $tab->getOrderNumber());
            $em->flush();
            $this->checkOrderNumber($oldOrderNumber, $tab->getOrderNumber(), $tab->getUser()->getId());
            $em->flush();
            return $tab;
        } else {
            return $form;
        }
    }

    private function checkOrderNumber($oldOrderNumber, $newOrderNumber, $user_id)
    {
        if ($oldOrderNumber < $newOrderNumber && $oldOrderNumber != 0) {
            $arr = $this->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Tab')->getTabsAsc($user_id);
        } else {
            $arr = $this->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Tab')->getTabsDesc($user_id);
        }

        $i = 1;
        foreach ($arr as $tab) {
            if ($i != $tab->getOrderNumber()) {
                $tab->setOrderNumber($i);
            }
            $i++;
        }
    }
}
