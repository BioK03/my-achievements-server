<?php

namespace CoreBundle\Controller\User;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use CoreBundle\Form\Type\AchievementType;
use CoreBundle\Entity\Achievement;
use CoreBundle\Controller\BaseController;

class AchievementController extends BaseController
{

    /**
     * Get all achievements for a achievement of a user
     * @ApiDoc(
     *  description="Get all achievements for a achievement of a user",
     *  section="4-Achievements",
     *  output={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"achievement"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"achievement"})
     * @Rest\Get("/users/{user_id}/tabs/{tabs_id}/achievements")
     */
    public function getAchievementsAction(Request $request)
    {
        $tab = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:Tab')
                ->find($request->get('tab_id'));

        if (empty($tab)) {
            return $this->tabNotFound();
        }
        if ($tab->getUser()->getId() != $user_id) {
            return $this->userNotCorrect();
        }

        return $tab->getAchievements();
    }

    /**
     * Add achievement(s) for a tab of a user
     * @ApiDoc(
     *  description="Add achievement(s) for a tab of a user",
     *  section="4-Achievements",
     *  input={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"post"}
     *  },
     *  output={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"achievement"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"achievement"})
     * @Rest\Post("/users/{user_id}/tabs/{tabs_id}/achievements")
     */
    public function postAchievementsAction(Request $request)
    {
        $tab = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:Tab')
                ->find($request->get('tab_id'));

        if (empty($tab)) {
            return $this->tabNotFound();
        }
        if ($tab->getUser()->getId() != $user_id) {
            return $this->userNotCorrect();
        }

        $achievement = new Achievement();
        $achievement->setTab($tab);
        $form = $this->createForm(AchievementType::class, $achievement);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($achievement);
            $em->flush();
            return $achievement;
        } else {
            return $form;
        }
    }

    /**
     * Remove a achievement by id for a tab of a user
     * @ApiDoc(
     *  description="Remove a achievement by id for a tab of a user",
     *  section="4-Achievements"
     * )
     *
     * @Rest\Delete("/users/{user_id}/tabs/{tabs_id}/achievements/{achievement_id}")
     */
    public function removeAchievementAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $achievement = $em->getRepository('CoreBundle:Achievement')
                ->find($request->get('achievement_id'));

        if ($achievement) {
            if ($achievement->getTab()->getId() != $tab_id) {
                $this->tabNotCorrect();
            }
            if ($achievement->getTab()->getUser()->getId() != $user_id) {
                return $this->userNotCorrect();
            }
            $em->remove($achievement);
            $em->flush();
        }
    }

    /**
     * Complete update of a achievement of a tab of a user
     * @ApiDoc(
     *  description="Complete update of a achievement of a tab of a user",
     *  section="4-Achievements",
     *  input={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"achievement"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"achievement"})
     * @Rest\Put("/users/{user_id}/tabs/{tabs_id}/achievements/{achievement_id}")
     */
    public function updateAchievementAction(Request $request)
    {
        return $this->updateAchievement($request, true);
    }

    /**
     * Partial update of a achievement of a tab of a user
     * @ApiDoc(
     *  description="Partial update of a achievement of a tab of a user",
     *  section="4-Achievements",
     *  input={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"achievement"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"achievement"})
     * @Rest\Patch("/users/{user_id}/tabs/{tabs_id}/achievements/{achievement_id}")
     */
    public function patchAchievementAction(Request $request)
    {
        return $this->updateAchievement($request, false);
    }

    private function updateAchievement(Request $request, $clearMissing)
    {
        $achievement = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:Achievement')
                ->find($request->get('achievement_id'));

        if (empty($achievement)) {
            return $this->achievementNotFound();
        }
        if ($achievement->getTab()->getUser()->getId() != $user_id) {
            return $this->userNotCorrect();
        }
        if ($achievement->getTab()->getId() != $tab_id) {
            return $this->tabNotCorrect();
        }

        $form = $this->createForm(AchievementType::class, $achievement);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($achievement);
            $em->flush();
            return $achievement;
        } else {
            return $form;
        }
    }
}
