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
     * @Rest\Post("/deleteunusedfiles")
     */
    public function postDeleteUnusedFilesAction(Request $request)
    {
        $em = $this->get('doctrine.orm.entity_manager');
        $res = $em->getRepository('CoreBundle:User')->getAllFiles();
        $files = [];
        foreach ($res as $re) {
            if ($re['profilePicture'] != null) {
                $files[] = bsename($re['profilePicture']);
            }
        }

        $res = $em->getRepository('CoreBundle:Achievement')->getAllFiles();
        foreach ($res as $re) {
            foreach ($re['images'] as $img) {
                if ($img != null) {
                    $files[] = basename($img);
                }
            }
        }
        $serverFiles = scandir(__DIR__.'/../../../web/uploads');
        foreach ($serverFiles as $file) {
            if ($file != "." && $file != ".." && !in_array($file, $files)) {
                unlink(__DIR__.'/../../../web/uploads/'.$file);
            }
        }
        return \FOS\RestBundle\View\View::create(['message' => "Unused Files deleted"], Response::HTTP_OK);
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
            if (!strstr($file->getClientMimeType(), "image")) {
                return \FOS\RestBundle\View\View::create(['message' => "The file must be an image", "type" => $file->getClientMimeType()], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            if ($file->getClientSize() > 1999000) {
                return \FOS\RestBundle\View\View::create(['message' => "The file is too big"], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $paths[] = 'uploads/'.$this->upload($file, $file->getClientOriginalName());
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
