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
use CoreBundle\Entity\File;
use CoreBundle\Form\Type\CredentialsType;
use CoreBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class UserController extends BaseController
{

    /**
     * Get a profile user by id
     * @ApiDoc(
     *  description="Get a profile user by id",
     *  section="2-Users (Public)",
     *  output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"user"},
     *      "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"user"})
     * @Rest\Get("/userprofiles/{user_id}")
     */
    public function getPublicUserAction(Request $request)
    {
        $user = $this->get('doctrine.orm.entity_manager')
                ->getRepository('CoreBundle:User')
                ->find($request->get('user_id'));

        if (empty($user)) {
            return $this->userNotFound();
        }
        if ($request->getSession()->get('user_id')) {
            return $user;
        } else {
            $ret = [
                'id' => $user->getId(),
                'firstname' => $user->getFirstname(),
                'lastname' => $user->getLastname(),
                'email' => $user->getEmail(),
                'profilePicture' => $user->getProfilePicture(),
                'nbAchievements' => $user->getNbAchievements(),
                'tabs' => []
            ];

            foreach ($user->getTabs() as $t) {
                $tab = [
                    'id' => $t->getId(),
                    'name' => $t->getName(),
                    'color' => $t->getColor(),
                    'orderNumber' => $t->getOrderNumber(),
                    'icon' => $t->getIcon(),
                    'achievements' => []
                ];
                $achievements = $this->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:Achievement')->findBy(['tab' => $t, 'favorite' => true], ['orderNumber' => 'desc'], 3, 0);
                foreach ($achievements as $a) {
                    $tab['achievements'][] = [
                        'id' => $a->getId(),
                        'name' => $a->getName(),
                        'orderNumber' => $a->getOrderNumber(),
                        'icon' => $a->getIcon(),
                        'shortdesc' => $a->getShortdesc(),
                        'longdesc' => $a->getLongdesc(),
                        'favorite' => $a->getFavorite(),
                    ];
                }
                $ret['tabs'][] = $tab;
            }
            return \FOS\RestBundle\View\View::create($ret, Response::HTTP_OK);
        }
    }

    /**
     * Add a user (=sign in)
     * @ApiDoc(
     *  description="Add a user",
     *  section="2-Users",
     *  input={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"post"}
     *  },
     * output={
     *      "class"="CoreBundle\Entity\User"
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"user"})
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
                }
            }
            $encoder = $this->get('security.password_encoder');
            $encoded = $encoder->encodePassword($user, $user->getPlainPassword());
            $user->setPassword($encoded);
            $user->calculNbAchievements();
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
     *  section="2-Users",
     *  output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"user"},
     *      "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
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
     * Get a user by searching his firstname or lastname
     * @ApiDoc(
     *  description="Get a user by searching his firstname or lastname",
     *  section="2-Users (Public)",
     *  output={
     *      "class"="CoreBundle\Entity\User",
     *      "groups"={"search"},
     *      "parsers"={"Nelmio\ApiDocBundle\Parser\JmsMetadataParser"}
     *  }
     * )
     *
     * @Rest\View(serializerGroups={"search"})
     * @Rest\Get("/search/{words}")
     */
    public function getSearchAction(Request $request)
    {
        $arr = explode(" ", $request->get('words'));

        return $this->get('doctrine.orm.entity_manager')->getRepository('CoreBundle:User')->search($arr);
    }

    /**
     * Remove a user by id
     * @ApiDoc(
     *  description="Remove a user by id",
     *  section="2-Users")
     *
     * @Rest\View(statusCode=Response::HTTP_NO_CONTENT)
     * @Rest\Delete("/users/{user_id}")
     */
    public function deleteUserAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('CoreBundle:User')
                ->find($request->get('user_id'));

        if ($user) {
            $em->remove($user);
            $em->flush();
        }
    }

    /**
     * Complete update of a user
     * @ApiDoc(
     *  description="Complete update of a user",
     *  section="2-Users",
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
     *  section="2-Users",
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
                ->getRepository('CoreBundle:User')
                ->find($request->get('user_id'));

        if (empty($user)) {
            return $this->userNotFound();
        }

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all(), $clearMissing);

        if ($form->isValid()) {
            $em = $this->get('doctrine.orm.entity_manager');
            $user->calculNbAchievements();
            $em->flush();
            return $user;
        } else {
            return $form;
        }
    }

    /**
     * Upload the profile picture of a user
     * Return :
     * ['paths' => array of path]
     *
     * @ApiDoc(
     *  description="Upload the profile picture of a user",
     *  section="2-Users"
     * )
     *
     * @Rest\Post("/users/{user_id}/upload")
     */
    public function postUserUploadAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('CoreBundle:User')->find($request->get('user_id'));
        if (empty($user)) {
            return $this->userNotFound();
        }
        $files = $request->files;
        $paths = [];
        foreach ($files as $upFile) {
            if ($user->getProfilePicture() == null) {
                $file = new File();
                $user->setProfilePicture($file);
                $em->persist($file);
            }
            $file->setFile($upFile);
            $test = hash_file("sha512", $file->getFile()).'.'.$file->getFile()->guessExtension();
            return \FOS\RestBundle\View\View::create(['debug' => $test], Response::HTTP_OK);
            $em->flush();
            $paths[] = $file->getWebPath();
        }
        return \FOS\RestBundle\View\View::create(['paths' => $paths], Response::HTTP_OK);
    }
}
