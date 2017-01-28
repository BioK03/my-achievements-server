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

class SecurityController extends BaseController
{

    /**
     * Login
     * Return :
     * ['message' => "Login sucess",
     * 'id' => user id,
     * 'firstname' => use firstname,
     * 'lastname' => user lastname,
     * 'email' => user email,
     * 'publicPicture' => user profilePicture]
     *
     * @ApiDoc(
     *  description="Login",
     *  section="1-Security",
     *  input={
     *      "class"="CoreBundle\Entity\Credentials"
     *  }
     * )
     *
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
        $ret = [
            'message' => "Login success",
            'id' => $user->getId(),
            'firstname' => $user->getFirstname(),
            'lastname' => $user->getLastname(),
            'email' => $user->getEmail(),
            'profilePicture' => $user->getProfilePicture(),
            'nbAchievements' => $user->getNbAchievements()
        ];
        return \FOS\RestBundle\View\View::create($ret, Response::HTTP_OK);
    }

    /** Logout
     * @ApiDoc(
     *  description="Logout",
     *  section="1-Security"
     * )
     *
     * @Rest\DELETE("/logout")
     */
    public function deleteLogoutAction(Request $request)
    {
        $request->getSession()->remove("user_id");
        return $this->ok("Logout success");
    }

    /** Check connection for a user
     * @ApiDoc(
     *  description="Check connection for a user",
     *  section="1-Security"
     * )
     *
     * @Rest\GET("/connected")
     */
    public function getConnectedAction(Request $request)
    {
        if ($request->getSession()->get('user_id')) {
            return $this->ok(true);
        } else {
            return $this->ok(false);
        }
    }
}
