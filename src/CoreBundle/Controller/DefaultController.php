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
     * @Rest\Post("/deleteUnusedFile")
     */
    public function postDeleteUnusedFile(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $files = $em->getRepository('CoreBundle:User')
                ->getAllFiles();
        array_merge($files, $em->getRepository('CoreBundle:Achievement')->getAllFiles());
        $serverFiles = scandir("http://localhost:8100/uploads/");
        return \FOS\RestBundle\View\View::create(['files' => $files, 'serverFiles' => $serverFiles], Response::HTTP_OK);
    }

    /**
     * Upload a file on the server
     * Return :
     * ['paths' => array of path]
     *
     * @ApiDoc(
     *  description="Upload a file on the server",
     *  section="0-Default"
     * )
     *
     * @Rest\Post("/upload")
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
