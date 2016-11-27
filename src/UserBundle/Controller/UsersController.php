<?php

namespace UserBundle\Controller;

use UserBundle\Entity\Repository\UserVisitRepository;
use UserBundle\Entity\User;
use UserBundle\Entity\Repository\UserRepository;
use UserBundle\Entity\UserVisit;
use UserBundle\Form\Type\UserType;
use FOS\RestBundle\View\View;
use FOS\RestBundle\Controller\Annotations;
use FOS\RestBundle\Controller\FOSRestController;
use FOS\RestBundle\Routing\ClassResourceInterface;
use FOS\RestBundle\Controller\Annotations\RouteResource;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Symfony\Component\Form\Extension\Core\Type\IntegerType;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Form\FormTypeInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\ParamConverter;

/**
 * Class UsersController
 * @package UserBundle\Controller
 *
 * @RouteResource("user")
 */
class UsersController extends FOSRestController
{
    /**
     * Gets an individual User
     *
     * @param int $id User id
     * @return mixed
     * @throws \Doctrine\ORM\NoResultException
     * @throws \Doctrine\ORM\NonUniqueResultException
     *
     * @ApiDoc(
     *     output="UserBundle\Entity\User",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function getAction($id)
    {
        /**
         * @var $user User
         */
        $user = $this->getUserRepository()->createFindOneByIdQuery($id)->getOneOrNullResult();

        if ($user === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        return $user;
    }

    /**
     * Gets a collection of Users
     *
     * @return array
     *
     * @ApiDoc(
     *     output="UserBundle\Entity\User",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function cgetAction()
    {
        return $this->getUserRepository()->createFindAllQuery()->getResult();
    }

    /**
     * Create new User
     *
     * @param Request $request
     * @return View|\Symfony\Component\Form\Form
     *
     * @ApiDoc(
     *     input="UserBundle\Form\Type\UserType",
     *     statusCodes={
     *         201 = "Returned when a new User has been successful created",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function postAction(Request $request)
    {
        $form = $this->createForm(UserType::class, null);

        $form->submit($request->request->all());

        if (!$form->isValid()) {
            return $form;
        }

        /**
         * @var $user User
         */
        $user = $form->getData();

        $em = $this->getDoctrine()->getManager();
        $em->persist($user);
        $em->flush();

        $routeOptions = [
            'id' => $user->getId(),
            '_format' => $request->get('_format'),
        ];

        return $this->routeRedirectView('get_user', $routeOptions, Response::HTTP_CREATED);
    }

    /**
     * Update an existing User
     *
     * @param Request $request
     * @param int $id User id
     * @return View|\Symfony\Component\Form\Form
     *
     * @ApiDoc(
     *     input="UserBundle\Form\Type\UserType",
     *     statusCodes={
     *         204 = "Returned when an existing User has been successful updated",
     *         400 = "Return when errors",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function putAction(Request $request, $id)
    {
        /**
         * @var $user User
         */
        $user = $this->getUserRepository()->find($id);

        if ($user === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $form = $this->createForm(UserType::class, $user);

        $form->submit($request->request->all(), false);

        if (!$form->isValid()) {
            return $form;
        }

        $em = $this->getDoctrine()->getManager();
        $em->flush();

        $routeOptions = [
            'id' => $user->getId(),
            '_format' => $request->get('_format'),
        ];

        return $this->routeRedirectView('get_user', $routeOptions, Response::HTTP_NO_CONTENT);
    }

    /**
     * Delete user
     *
     * @param int $id User id
     * @return View
     *
     * @ApiDoc(
     *     statusCodes={
     *         204 = "Returned when an existing User has been successful deleted",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function deleteAction($id)
    {
        /**
         * @var $user User
         */
        $user = $this->getUserRepository()->find($id);

        if ($user === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Registers the user's visit
     *
     * @param int $id User id
     * @return View|\Symfony\Component\Form\Form
     *
     * @ApiDoc(
     *     statusCodes={
     *         204 = "Returned when a User's visit has been successful registered",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function postVisitAction($id)
    {
        /**
         * @var $user User
         */
        $user = $this->getUserRepository()->find($id);

        if ($user === null) {
            return new View(null, Response::HTTP_NOT_FOUND);
        }

        $visit = new UserVisit($user);
        $em = $this->getDoctrine()->getManager();
        $em->persist($visit);
        $em->flush();

        return new View(null, Response::HTTP_NO_CONTENT);
    }

    /**
     * Gets count of daily active users
     *
     * @param Request $request
     * @return int
     *
     * @ApiDoc(
     *     requirements={
     *         {
     *              "name"="from",
     *              "dataType"="string",
     *              "description"="Start date in format YYYY-MM-DD",
     *         },
     *         {
     *              "name"="to",
     *              "dataType"="string",
     *              "description"="End date in format YYYY-MM-DD",
     *         }
     *     },
     *     output="int",
     *     statusCodes={
     *         200 = "Returned when successful",
     *         404 = "Return when not found"
     *     }
     * )
     */
    public function dauAction(Request $request)
    {
        $from = $request->get('from');
        if ($from === null) {
            return new View('missing a required parameter "from"', Response::HTTP_BAD_REQUEST);
        }

        $to = $request->get('to');
        if ($to === null) {
            return new View('missing a required parameter "to"', Response::HTTP_BAD_REQUEST);
        }

        $format = 'Y-m-d';

        $startDate = \DateTime::createFromFormat($format, $from);
        if ($startDate === false) {
            return new View('invalid format of parameter "from"', Response::HTTP_BAD_REQUEST);
        }

        $endDate = \DateTime::createFromFormat($format, $to);
        if ($endDate === false) {
            return new View('invalid format of parameter "to"', Response::HTTP_BAD_REQUEST);
        }

        $result = $this->getUserVisitRepository()->createDAUQuery($startDate, $endDate)->getOneOrNullResult();

        return $result === null ? 0 : (int)$result['uniqueCount'];
    }

    /**
     * Returns user repository
     *
     * @return UserRepository
     */
    private function getUserRepository()
    {
        return $this->get('app.doctrine_entity_repository.user');
    }

    /**
     * Returns user visits repository
     *
     * @return UserVisitRepository
     */
    private function getUserVisitRepository()
    {
        return $this->get('app.doctrine_entity_repository.user_visit');
    }
}