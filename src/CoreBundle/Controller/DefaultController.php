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
     *  section="0-Default"
     * )
     *
     * @Rest\View(statusCode=Response::HTTP_OK)
     * @Rest\Get("/")
     */
    public function indexAction()
    {
        return $this->render('CoreBundle:Default:index.html.twig');
    }

    /**
     * Delete all unused files
     *
     * @ApiDoc(
     *  description="Delete all unused files",
     *  section="0-Default"
     * )
     *
     * @Rest\Post("/deleteunusedfile")
     */
    public function postDeleteUnusedFileAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $res = $em->getRepository('CoreBundle:User')->getAllFiles();
        $files = [];
        foreach ($res as $re) {
            if ($re['profilePicture'] != null) {
                $files[] = $re['profilePicture'];
            }
        }

        $res = $em->getRepository('CoreBundle:Achievement')->getAllFiles();
        foreach ($res as $re) {
            foreach ($re['images'] as $img) {
                if ($img != null) {
                    $files[] = $img;
                }
            }
        }
        $serverFiles = scandir(__DIR__.'/../../../web/uploads');
        return \FOS\RestBundle\View\View::create(['files' => $files, 'serverFiles' => $serverFiles, 'test' => basename($files[0])], Response::HTTP_OK);
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
    public function postUploadAction(Request $request)
    {
        $files = $request->files;
        $paths = [];
        foreach ($files as $file) {
            $paths[] = 'uploads/'.$this->upload($file, $file->getCLientOriginalName());
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
