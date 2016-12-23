<?php

namespace CoreBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;

class BaseController extends Controller
{

    protected function tabNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Tab not found'], Response::HTTP_NOT_FOUND);
    }

    protected function achievementNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Achievement not found'], Response::HTTP_NOT_FOUND);
    }

    protected function userNotCorrect()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'The tab of this achievement not belong to this user.'], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    protected function tabNotCorrect()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'This achievement not belong to this tab.'], Response::HTTP_METHOD_NOT_ALLOWED);
    }

    protected function userNotFound()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'User not found'], Response::HTTP_NOT_FOUND);
    }

    protected function invalidCredentials()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Invalid credentials'], Response::HTTP_BAD_REQUEST);
    }

    protected function ok($msg)
    {
        return \FOS\RestBundle\View\View::create(['message' => $msg], Response::HTTP_OK);
    }

    protected function replayAttackError()
    {
        return \FOS\RestBundle\View\View::create(['message' => 'Too many connection attemps'], Response::HTTP_TOO_MANY_REQUESTS);
    }
}
