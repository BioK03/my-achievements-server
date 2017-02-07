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
use Google_Client;
use Google_Service_Oauth2;

class SecurityController extends BaseController
{

    /**
     * Login
     * Return :
     * ['message' => "Login success",
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
     * Return :
     * ['message' => "Logout success"]
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
     * Return :
     * ['message' => boolean]
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

    /**
     * Redirect to google OAuth
     * Return :
     * ['message' => auth url]
     *
     * @ApiDoc(
     *  description="Redirect to google OAuth",
     *  section="1-Security"
     * )
     *
     * @Rest\Get("/googleredirect")
     */
    public function getGoogleRedirectAction(Request $request)
    {
        $client = new Google_Client();
        $client->setAuthConfigFile(__DIR__.'/../Security/client_id.json');
        $client->setRedirectUri('http://localhost:8100/googlelogin');
        $client->addScope('profile');
        $client->addScope('email');

        $auth_url = $client->createAuthUrl();
        return \FOS\RestBundle\View\View::create(["message" => filter_var($auth_url, FILTER_SANITIZE_URL)], Response::HTTP_OK);
    }

    /**
     * Login with google
     * Return :
     * 'id' => user id,
     * 'firstname' => use firstname,
     * 'lastname' => user lastname,
     * 'email' => user email,
     * 'publicPicture' => user profilePicture
     * in getter parameter of the redirect url (base http://localhost:3000
     *
     * @ApiDoc(
     *  description="Login with google",
     *  section="1-Security"
     * )
     *
     * @Rest\Get("/googlelogin")
     */
    public function getGoogleLoginAction(Request $request)
    {

        $client = new Google_Client();
        $client->setAuthConfigFile(__DIR__.'/../Security/client_id.json');
        $client->addScope('profile');
        $client->addScope('email');
        $url = "http://localhost:3000/login";

        $token = $client->fetchAccessTokenWithAuthCode($request->get('code'));
        $client->setAccessToken($token);

        $google_oauth = new Google_Service_Oauth2($client);
        $email = $google_oauth->userinfo->get()->email;
        $firstname = $google_oauth->userinfo->get()->familyName;
        $lastname = $google_oauth->userinfo->get()->givenName;

        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('CoreBundle:User')
                ->findOneByEmail($email);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $em->persist($user);
            $em->flush();
        }
        $request->getSession()->set("user_id", $user->getId());
        $url .= "?id=".$user->getId().
                "&firstname=".$user->getFirstname().
                "&lastname=".$user->getLastname().
                "&email=".$user->getEmail().
                "&profilePicture=".$user->getProfilePicture().
                "&nbAchievements=".$user->getNbAchievements();
        return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
    }

    /**
     * Redirect to linkedin OAuth
     * Return :
     * ['message' => auth url]
     *
     * @ApiDoc(
     *  description="Redirect to linkedin OAuth",
     *  section="1-Security"
     * )
     *
     * @Rest\Get("/linkedinredirect")
     */
    public function getLinkedinRedirectAction(Request $request)
    {
        $url = "https://www.linkedin.com/oauth/v2/authorization?response_type=code&client_id=77qre7pyi9p5av&redirect_uri=http://localhost:8100/linkedinlogin&state=5d2c8b4c9m1a&scope=r_basicprofile%20r_emailaddress";

        return \FOS\RestBundle\View\View::create(["message" => filter_var($url, FILTER_SANITIZE_URL)], Response::HTTP_OK);
    }

    /**
     * Login with linkedin
     * Return :
     * 'id' => user id,
     * 'firstname' => use firstname,
     * 'lastname' => user lastname,
     * 'email' => user email,
     * 'publicPicture' => user profilePicture
     * in getter parameter of the redirect url (base http://localhost:3000
     *
     * @ApiDoc(
     *  description="Login with linkedin",
     *  section="1-Security"
     * )
     *
     * @Rest\Get("/linkedinlogin")
     */
    public function getLinkedinLoginAction(Request $request)
    {
        $url = "http://localhost:3000/login";

        $fields = array(
            'grant_type' => "authorization_code",
            'code' => $request->get('code'),
            'redirect_uri' => "http://localhost:8100/linkedinlogin",
            'client_id' => "77qre7pyi9p5av",
            'client_secret' => "vHNdT4RZ9hyJGYFg"
        );
        $fields_string = "";

        foreach ($fields as $key => $value) {
            $fields_string .= $key.'='.$value.'&';
        }
        rtrim($fields_string, '&');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://www.linkedin.com/oauth/v2/accessToken");
        curl_setopt($ch, CURLOPT_POST, count($fields));
        curl_setopt($ch, CURLOPT_POSTFIELDS, $fields_string);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/x-www-form-urlencoded']);
        $response = curl_exec($ch);
        curl_close($ch);
        $token = json_decode($response)->access_token;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.linkedin.com/v1/people/~:(email-address)?format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
        $response = curl_exec($ch);
        curl_close($ch);
        $email = json_decode($response)->emailAddress;

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, "https://api.linkedin.com/v1/people/~?format=json");
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Authorization: Bearer '.$token]);
        $response = curl_exec($ch);
        curl_close($ch);
        $firstname = json_decode($response)->firstName;
        $firstname = json_decode($response)->lastName;

        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('CoreBundle:User')
                ->findOneByEmail($email);

        if (!$user) {
            $user = new User();
            $user->setEmail($email);
            $user->setFirstname($firstname);
            $user->setLastname($lastname);
            $em->persist($user);
            $em->flush();
        }
        $request->getSession()->set("user_id", $user->getId());
        $url .= "?id=".$user->getId().
                "&firstname=".$user->getFirstname().
                "&lastname=".$user->getLastname().
                "&email=".$user->getEmail().
                "&profilePicture=".$user->getProfilePicture().
                "&nbAchievements=".$user->getNbAchievements();
        return new \Symfony\Component\HttpFoundation\RedirectResponse($url);
    }
}
