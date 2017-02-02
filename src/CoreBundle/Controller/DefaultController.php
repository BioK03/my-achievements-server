<?php

namespace CoreBundle\Controller;

use CoreBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\HttpFoundation\File\UploadedFile;

class DefaultController extends BaseController
{

    /** Default
     * @ApiDoc(
     *  description="Default",
     *  section=" 0-Default"
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\GET("/")
     */
    public function indexAction()
    {
        return $this->render('CoreBundle:Default:index.html.twig');
    }

    /**
     *
     * @Rest\Get("/debug/{user_id}")
     */
    public function getDebugAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $user = $em->getRepository('CoreBundle:User')
                ->findOneById($request->get('user_id'));
        $user->calculNbAchievements();
        $em->flush();
        return $this->ok('debug');
    }
}
