<?php

namespace RobotsBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class RobotController extends Controller
{
    public function listAction()
    {
        $robots = $this->getRobotRepository()->findBy(array(), array('createdAt' => 'DESC'));

        return $this->render('RobotsBundle:Robot:list.html.twig',[
            'robots' => $robots
        ]);
    }

    public function notImplementedAction($action)
    {
        return new JsonResponse([
            'status' => Response::$statusTexts[Response::HTTP_NOT_IMPLEMENTED]
        ], Response::HTTP_NOT_IMPLEMENTED);
    }

    private function getRobotRepository()
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository('RobotsBundle:Robot');
    }

}
