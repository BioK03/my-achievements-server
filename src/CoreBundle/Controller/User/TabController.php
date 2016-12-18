<?php

namespace CoreBundle\Controller\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use CoreBundle\Form\Type\TabType;
use CoreBundle\Entity\Tab;

class TabController extends Controller
{

    /**
     * Get all tabs for a user
     * @ApiDoc(
     *  description="Get all tabs for a user",
     *  section="tabs",
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
     * Add tab(s) for a user
     * @ApiDoc(
     *  description="Add tab(s) for a user",
     *  section="tabs",
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
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"tab"})
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
            $em->persist($tab);
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
     *  section="tabs")
     *
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{user_id}/tabs/{tab_id}")
     */
    public function removeTabAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $tab = $em->getRepository('CoreBundle:Tab')
                ->find($request->get('tab_id'));

        if ($tab) {
            if ($tab->getUser()->getId() != $user_id) {
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
     *  section="tabs",
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
     *  section="tabs",
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
                ->getRepository('AppBundle:Tab')
                ->find($request->get('tab_id'));

        if (empty($tab)) {
            return $this->tabNotFound();
        }

        if ($tab->getUser()->getId() != $user_id) {
            return $this->userNotCorrect();
        }

        $form = $this->createForm(TabType::class, $tab);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($tab);
            $em->flush();
            return $tab;
        } else {
            return $form;
        }
    }

    private function userNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }

    private function tabNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Tab not found'], Response::HTTP_NOT_FOUND);
    }

    private function userNotCorrect()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'This tab not belong to this user.'], Response::HTTP_METHOD_NOT_ALLOWED);
    }
}
