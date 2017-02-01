<?php

namespace CoreBundle\Controller;

use CoreBundle\Controller\BaseController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use FOS\RestBundle\Controller\Annotations as Rest;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;

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

    /**
     *
     * @Rest\Post("/test")
     */
    public function postTestAction(Request $request)
    {
        $files = $request->files;
        $paths = [];
        foreach ($files as $file) {
            $paths[] = 'http://localhost:8100/uploads/'.$this->upload($file, $file->getCLientOriginalName());
        }
        return \FOS\RestBundle\View\View::create(['paths' => $paths], Response::HTTP_OK);
    }

    public function upload(UploadedFile $file, $name)
    {
        $parts = explode(".", $name);

        $fileName = md5(uniqid()).'.'.$parts[count($parts) - 1];

        $file->move(__DIR__.'/../../../web/uploads', $fileName);

        return $fileName;
    }
}
