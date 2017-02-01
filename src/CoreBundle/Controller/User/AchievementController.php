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
     * Get all achievements for a tab of a user
     * @ApiDoc(
     *  description="Get all achievements for a tab of a user",
     *  section="4-Achievements",
     *  output={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"achievement"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"achievement"})
     * @Rest\Get("/users/{user_id}/tabs/{tab_id}/achievements")
     */
    public function getAchievementsAction(Request $request)
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

        return $tab->getAchievements();
    }

    /**
     * Get all favorite achievements for a tab of a user
     * @ApiDoc(
     *  description="Get all favorite achievements for a tab of a user",
     *  section="4-Achievements",
     *  output={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"achievement"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"achievement"})
     * @Rest\Get("/users/{user_id}/tabs/{tab_id}/favoriteachievements")
     */
    public function getFavoriteAchievementsAction(Request $request)
    {
        $achievements = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:Achievement')
                ->findBy(array('tab' => $request->get('tab_id'), 'favorite' => true));

        if (empty($achievements)) {
            return $this->achievementNotFound();
        }
        foreach ($achievements as $a) {
            if ($a->getTab()->getId() != $request->get('tab_id')) {
                return $this->tabNotCorrect();
            }
            if ($a->getTab()->getUser()->getId() != $request->get('user_id')) {
                return $this->userNotCorrect();
            }
        }

        return $achievements;
    }

    /**
     * Get a achievement by id
     * @ApiDoc(
     *  description="Get a achievement by id",
     *  section="4-Achievements",
     *  output={
     *      "class"="CoreBundle\Entity\Achievement",
     *      "groups"={"achievement"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"achievement"})
     * @Rest\Get("/users/{user_id}/tabs/{tab_id}/achievements/{achievement_id}")
     */
    public function getAchievementAction(Request $request)
    {
        $achievement = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:Achievement')
                ->find($request->get('achievement_id'));

        if (empty($achievement)) {
            return $this->achievementNotFound();
        }
        if ($achievement->getTab()->getUser()->getId() != $request->get('user_id')) {
            return $this->userNotCorrect();
        }
        if ($achievement->getTab()->getId() != $request->get('tab_id')) {
            return $this->tabNotCorrect();
        }

        return $achievement;
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
     * @Rest\Post("/users/{user_id}/tabs/{tab_id}/achievements")
     */
    public function postAchievementsAction(Request $request)
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

        $achievement = new Achievement();
        $achievement->setTab($tab);
        $form = $this->createForm(AchievementType::class, $achievement);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $achievement->defaultValues(true);
            $em->persist($achievement);
            $em->flush();
            $this->checkOrderNumber(0, $achievement->getOrderNumber(), $achievement->getTab()->getId());
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
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{user_id}/tabs/{tab_id}/achievements/{achievement_id}")
     */
    public function deleteAchievementAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $achievement = $em->getRepository('CoreBundle:Achievement')
                ->find($request->get('achievement_id'));

        if ($achievement) {
            if ($achievement->getTab()->getId() != $request->get('tab_id')) {
                return $this->tabNotCorrect();
            }
            if ($achievement->getTab()->getUser()->getId() != $request->get('user_id')) {
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
     * @Rest\Put("/users/{user_id}/tabs/{tab_id}/achievements/{achievement_id}")
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
     * @Rest\Patch("/users/{user_id}/tabs/{tab_id}/achievements/{achievement_id}")
     */
    public function patchAchievementAction(Request $request)
    {
        return $this->updateAchievement($request, false);
    }

    private function updateAchievement(Request $request, $clearMissing)
    {
        $achievement = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:Achievement')
                ->find($request->get('achievement_id'));

        if (empty($achievement)) {
            return $this->achievementNotFound();
        }
        if ($achievement->getTab()->getUser()->getId() != $request->get('user_id')) {
            return $this->userNotCorrect();
        }
        if ($achievement->getTab()->getId() != $request->get('tab_id')) {
            return $this->tabNotCorrect();
        }

        $oldOrderNumber = $achievement->getOrderNumber();
        $form = $this->createForm(AchievementType::class, $achievement);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $achievement->defaultValues($oldOrderNumber != $achievement->getOrderNumber());
            $em->flush();
            $this->checkOrderNumber($oldOrderNumber, $achievement->getOrderNumber(), $achievement->getTab()->getId());
            $em->flush();
            return $achievement;
        } else {
            return $form;
        }
    }

    private function checkOrderNumber($oldOrderNumber, $newOrderNumber, $tab_id)
    {
        if ($oldOrderNumber < $newOrderNumber && $oldOrderNumber != 0) {
            $arr = $this->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Achievement')->getAchievementsAsc($tab_id);
        } else {
            $arr = $this->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Achievement')->getAchievementsDesc($tab_id);
        }

        $i = 1;
        foreach ($arr as $ach) {
            if ($i != $ach->getOrderNumber()) {
                $ach->setOrderNumber($i);
            }
            $i++;
        }
    }
}
