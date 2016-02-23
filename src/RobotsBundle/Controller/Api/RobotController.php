<?php

namespace RobotsBundle\Controller\Api;

use RobotsBundle\Entity\Robot;
use RobotsBundle\Form\RobotType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Form\Form;
//Serializer
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

class RobotController extends Controller
{
    private $serializer = null;

    /**
     * Description: Összes robot lekérése
     * @return JsonResponse
     */
    public function listAction()
    {

        $robots = $this->getRobotRepository()->findAll();

        $responseArray = array();

        if($robots)
        {
            foreach($robots as $robot) {
                $responseArray[] = $this->serializeRobot($robot);
            }

            return new JsonResponse([
                'status' => 'OK',
                'data' => $responseArray
            ],Response::HTTP_OK);
        }
        else
        {
            return new JsonResponse([
                'status' => 'OK',
                'data' => $responseArray
            ],Response::HTTP_OK);
        }
    }

    /**
     * Description: Robotok keresése név alapján
     * @param $name
     * @return JsonResponse
     */
    public function searchAction($name)
    {
        $robots = $this->getRobotRepository()->searchByName($name);

        $responseArray = array();

        if($robots)
        {
            foreach($robots as $robot) {
                $responseArray[] = $this->serializeRobot($robot);
            }

            return new JsonResponse([
                'status' => 'FOUND',
                'data' => $responseArray
            ],Response::HTTP_OK);

        }
        else
        {
            return new JsonResponse([
                'status' => 'NOT-FOUND'
            ],Response::HTTP_OK);

        }
    }

    /**
     * Description: Robotok lehetséges típusainak lekérése
     * @return JsonResponse
     */
    public function filtersAction()
    {

        $responseArray = Robot::getTypes();

        if(!empty($responseArray))
        {
            return new JsonResponse([
                'status' => 'FOUND',
                'data' => $responseArray
            ],Response::HTTP_OK);
        }
        else
        {
            return new JsonResponse([
                'status' => 'NOT-FOUND'
            ],Response::HTTP_OK);
        }

    }

    /**
     * Description: Robotok szűrése típus alapján
     * @param: $type
     * @return JsonResponse
     */
    public function filterAction($type)
    {
        if(in_array($type, Robot::$types))
        {
            $filteredRobots = $this->getRobotRepository()->findByType($type);

            if($filteredRobots)
            {
                $data = [];

                foreach($filteredRobots as $robot) {
                    $data[] = $this->serializeRobot($robot);
                }

                return new JsonResponse([
                    'status' => 'FOUND',
                    'data' => $data
                ],Response::HTTP_OK);

            }
            else
            {
                return new JsonResponse([
                    'status' => 'NOT-FOUND'
                ],Response::HTTP_OK);
            }
        }
        else
        {
            return new JsonResponse([
                'status' => 'ERROR',
                'message' => 'Invalid filter value'
            ],Response::HTTP_BAD_REQUEST);

        }

    }

    /**
     * Description: Robot lekérése ID alapján
     * @param $id
     * @return JsonResponse
     */
    public function getAction($id)
    {
        $robot = $this->getRobotRepository()->findOneById($id);

        if($robot)
        {
            return new JsonResponse([
                'status' => 'FOUND',
                'data' => $this->serializeRobot($robot)
            ],Response::HTTP_OK);
        }
        else
        {
            return new JsonResponse([
                'status' => 'NOT-FOUND'
            ],Response::HTTP_OK);
        }

    }

    /**
     * Description: Új robot hozzáadása
     * @return JsonResponse
     */
    public function addNewAction()
    {

        $requestArray = $this->getJsonRequestData();

        $robot = new Robot( );

        $form = $this->createForm(new RobotType(), $robot);
        $form->submit($requestArray);

        if($form->isSubmitted() && $form->isValid())
        {
            $em = $this->getDoctrine()->getManager();
            $em->persist($robot);
            $em->flush();

            return new JsonResponse([
                'status' => 'CREATED',
                'data' => $this->serializeRobot($robot)
            ],Response::HTTP_CREATED);

        }
        else // hiba a validáció során
        {

            return new JsonResponse([
                'status' => 'ERROR',
                'message' => $this->getFormErrorsAsString($form)
            ],Response::HTTP_CONFLICT);

        }

    }

    /**
     * Description: Robot adatainak módosítása
     * @param $id
     * @return JsonResponse
     */
    public function updateAction($id)
    {

        $robot = $this->getRobotRepository()->findOneById($id);

        if($robot)
        {
            $requestArray = $this->getJsonRequestData();

            $robot = $this->getRobotRepository()->findOneById($id);

            $form = $this->createForm(new RobotType(), $robot);

            $formValues = array_merge($this->serializeRobot($robot),$requestArray);
            unset($formValues["id"]);

            $form->submit($formValues);

            if($form->isSubmitted() && $form->isValid())
            {
                $em = $this->getDoctrine()->getManager();
                $em->persist($robot);
                $em->flush();

                return new JsonResponse([
                    'status' => 'UPDATED',
                    'data' => $this->serializeRobot($robot)
                ],Response::HTTP_OK);

            }
            else // hiba a validáció során
            {
                return new JsonResponse([
                    'status' => 'ERROR',
                    'message' => $this->getFormErrorsAsString($form)
                ],Response::HTTP_CONFLICT);
            }
        }
        else
        {
            return new JsonResponse([
                'status' => 'ERROR',
                'message' => 'Entry does not exists'
            ],Response::HTTP_CONFLICT);
        }
    }

    /**
     * Description: Robot törlése
     * @param $id
     * @return JsonResponse
     */
    public function deleteAction($id)
    {
        $robot = $this->getRobotRepository()->findOneById($id);

        if($robot)
        {
            $em = $this->getDoctrine()->getManager();

            $em->getFilters()->enable('softdeleteable');

            $em->remove($robot);
            $em->flush();

            return new JsonResponse([
                'status' => 'DELETED'
            ],Response::HTTP_OK);

        }
        else
        {
            return new JsonResponse([
                'status' => 'ERROR',
                'message' => 'Entry does not exists'
            ],Response::HTTP_CONFLICT);
        }
    }


    private function getJsonRequestData()
    {
        $data = array();

        $content = $this->get('request')->getContent();

        if (!empty($content))
        {
            $data = json_decode($content, true);
        }

        return $data;
    }

    private function serializeRobot(Robot $robot)
    {
        if(is_null($this->serializer))
        {
            $encoders = array(new XmlEncoder(), new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());

            $this->serializer = new Serializer($normalizers, $encoders);
        }

        $array = json_decode($this->serializer->serialize($robot,'json'),true);

        unset($array["createdAt"]);
        unset($array["deletedAt"]);

        return $array;
    }



    public function getFormErrorsAsString($form)
    {
        $errorsAsString = "";

        $errors = $this->getFormErrors($form);

        if(!empty($errors)) {

            $errorsAsString = implode(' | ', array_map(function ($subErrorArray) {
                return implode(', ',$subErrorArray);
            }, $errors));

        }

        return $errorsAsString;
    }

    public function getFormErrors($form)
    {
        $errors = array();

        if ($form instanceof Form) {
            foreach ($form->getErrors() as $error) {
                $errors[] = $error->getMessage();
            }

            foreach ($form->all() as $key => $child) {
                if ($err = $this->getFormErrors($child)) {
                    $errors[$key] = $err;
                }
            }
        }

        return $errors;
    }


    private function getRobotRepository()
    {
        $em = $this->getDoctrine()->getManager();
        return $em->getRepository('RobotsBundle:Robot');
    }

}
