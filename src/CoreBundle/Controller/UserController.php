<?php

namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use CoreBundle\Form\Type\UserType;
use CoreBundle\Entity\User;

class UserController extends Controller
{

    /**
     * @Rest\View(serializerGroups={"post"})
     * @Rest\Get("/usersD")
     */
    public function getUsersDAction(Request $request)
    {
        return $this->getUsersAction($request);
    }

    /**
     * Get all users
     * @ApiDoc(
     *  description="Get all users",
     *  section="users",
     *  output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"user"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users")
     */
    public function getUsersAction(Request $request)
    {
        $users = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:User')
                ->findAll();

        return $users;
    }

    /**
     * Get a user by id
     * @ApiDoc(
     *  description="Get a user by id",
     *  section="users",
     *  output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"user"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/users/{user_id}")
     */
    public function getUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:User')
                ->find($request->get('user_id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        return $user;
    }

    /**
     * Add a user
     * @ApiDoc(
     *  description="Add a user",
     *  section="users",
     *  input={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"user"}
     *  }
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_CREATED, serializerGroups={"user"})
     * @Rest\Post("/users")
     */
    public function postUsersAction(Request $request)
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all());

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            foreach ($user->getTabs() as $tab) {
                $tab->setUser($user);
                $em->persist($tab);
            }
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * Remove a user by id
     * @ApiDoc(
     *  description="Remove a user by id",
     *  section="users")
     *
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{user_id}")
     */
    public function removeUserAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('CoreBundle:User')
                ->find($request->get('user_id'));

        if ($user) {
            foreach ($user->getTabs() as $tab) {
                $em->remove($tab);
            }
            $em->remove($user);
            $em->flush();
        }
    }

    /**
     * Complete update of a user
     * @ApiDoc(
     *  description="Complete update of a user",
     *  section="users",
     *  input={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"user"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Put("/users/{user_id}")
     */
    public function updateUserAction(Request $request)
    {
        return $this->updateUser($request, true);
    }

    /**
     * Partial update of a user.
     * @ApiDoc(
     *  description="Partial update of a user",
     *  section="users",
     *  input={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"user"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Patch("/users/{user_id}")
     */
    public function patchUserAction(Request $request)
    {
        return $this->updateUser($request, false);
    }

    private function updateUser(Request $request, $clearMissing)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('AppBundle:User')
                ->find($request->get('user_id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    private function userNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }
}
