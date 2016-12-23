<?php

namespace CoreBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use CoreBundle\Form\Type\UserType;
use CoreBundle\Entity\User;
use CoreBundle\Entity\Credentials;
use CoreBundle\Form\Type\CredentialsType;
use CoreBundle\Controller\BaseController;

class UserController extends BaseController
{

    /**
     * Add a user (=sign in)
     * @ApiDoc(
     *  description="Add a user",
     *  section="users",
     *  input={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\User"
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
                foreach ($tab->getAchievements() as $achievement) {
                    $achievement->setTab($tab);
                    $em->persist($achievement);
                }
                $em->persist($tab);
            }
            $encoder = $this->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);
            $em->persist($user);
            $em->flush();
            return $user;
        } else {
            return $form;
        }
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
     * Login
     * @ApiDoc(
     *  description="Login",
     *  section="security",
     *  input={
     *      "class"="CoreBundle\Entity\Credentials"
     *  }
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Post("/login")
     */
    public function postLoginAction(Request $request)
    {
        $credentials = new Credentials();
        $form = $this->createForm(CredentialsType::class, $credentials);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->get('doctrine.orm.entity_manager');

        $user = $em->getRepository('CoreBundle:User')
                ->findOneByEmail($credentials->getLogin());

        if (!$user) {
            return $this->invalidCredentials();
        }

        $encoder = $this->get('security.password_encoder');
        $isPasswordValid = $encoder->isPasswordValid($user, $credentials->getPassword());

        if (!$isPasswordValid) {
            if ($request->getSession()->get("connection_attemp") == null) {
                $request->getSession()->set("connection_attemp", 1);
            } else {
                $request->getSession()->set("connection_attemp", $request->getSession()->get("connection_attemp") + 1);
            }
            if ($request->getSession()->get("connection_attemp") >= 5) {
                return $this->replayAttackError();
            }
            return $this->invalidCredentials();
        }
        $request->getSession()->set("user_id", $user->getId());
        return $this->ok("Login succes");
    }

    /** Logout
     * @ApiDoc(
     *  description="Logout",
     *  section="security"
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Post("/users/{user_id}/logout")
     */
    public function postLogoutAction(Request $request)
    {
        $request->getSession()->remove("$user_id");
        return $this->ok("Logout succes");
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
}
