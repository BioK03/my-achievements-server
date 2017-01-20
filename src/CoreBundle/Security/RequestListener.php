<?php

namespace CoreBundle\Security;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;

class RequestListener
{

    public function onKernelRequest(GetResponseEvent $event)
    {

        $request = $event->getRequest();
        if (strstr($request->getUri(), "/users/")) {
            $temp = substr($request->getUri(), strpos($request->getUri(), "/users/") + 7);
            $user_id = substr($temp, 0, strpos($temp, "/") + 1);
            var_dump($user_id);

            if ($request->getSession()->get("user_id") == null) {
                $event->setResponse(new JsonResponse(array('message' => 'You are not connected'), Response::HTTP_PROXY_AUTHENTICATION_REQUIRED)); //HTTP_PROXY_AUTHENTICATION_REQUIRED
            } else if ($request->getSession()->get("user_id") != $user_id) {
                $event->setResponse(new JsonResponse(array('message' => 'You are not authorized to access to this user data'), Response::HTTP_BAD_REQUEST));
            }
        }
    }
}
