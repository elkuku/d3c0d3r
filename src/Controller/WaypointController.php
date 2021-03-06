<?php

namespace App\Controller;

use App\Entity\Waypoint;
use App\Form\WaypointFormTypeDetails;
use App\Form\WaypointType;
use App\Repository\WaypointRepository;
use App\Service\UploaderHelper;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/waypoint")
 */
class WaypointController extends AbstractController
{
    /**
     * @Route("/", name="waypoint_index", methods={"GET"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(WaypointRepository $waypointRepository): Response
    {
        return $this->render(
            'waypoint/index.html.twig',
            [
                'waypoints' => $waypointRepository->findAll(),
            ]
        );
    }

    /**
     * @Route("/new", name="waypoint_new", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function new(
        Request $request,
        UploaderHelper $uploaderHelper
    ): Response {
        $waypoint = new Waypoint();
        $form = $this->createForm(WaypointType::class, $waypoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadImage(
                    $uploadedFile,
                    $waypoint->getImageFilename()
                );
                $waypoint->setImageFilename($newFilename);
            }

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($waypoint);
            $entityManager->flush();

            return $this->redirectToRoute('waypoint_index');
        }

        return $this->render(
            'waypoint/new.html.twig',
            [
                'waypoint' => $waypoint,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}", name="waypoint_show", methods={"GET"}, requirements={"id"="\d+"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function show(Waypoint $waypoint): Response
    {
        return $this->render(
            'waypoint/show.html.twig',
            [
                'waypoint' => $waypoint,
            ]
        );
    }

    /**
     * @Route("/{id}/edit", name="waypoint_edit", methods={"GET","POST"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function edit(
        Request $request,
        Waypoint $waypoint,
        UploaderHelper $uploaderHelper
    ): Response {
        $form = $this->createForm(WaypointType::class, $waypoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            /** @var UploadedFile $uploadedFile */
            $uploadedFile = $form['imageFile']->getData();
            if ($uploadedFile) {
                $newFilename = $uploaderHelper->uploadImage(
                    $uploadedFile,
                    $waypoint->getImageFilename()
                );
                $waypoint->setImageFilename($newFilename);
            }
            // $newName = $this->getUploadedFileName($form);
            // if ($newName) {
            //     $waypoint->setImageFilename($newName);
            // }
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('waypoint_index');
        }

        return $this->render(
            'waypoint/edit.html.twig',
            [
                'waypoint' => $waypoint,
                'form'     => $form->createView(),
            ]
        );
    }

    /**
     * @Route("/{id}/edit-details", name="waypoints_edit_details")
     * @IsGranted("ROLE_ADMIN")
     */
    public function editDetails(Request $request, Waypoint $waypoint)
    {
        $form = $this->createForm(WaypointFormTypeDetails::class, $waypoint);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $waypoint = $form->getData();
            $em = $this->getDoctrine()->getManager();
            $em->persist($waypoint);
            $em->flush();
            $this->addFlash('success', 'Waypoint updated!');

            $redirectUri = $request->request->get('redirectUri');

            if ($redirectUri) {
                return $this->redirect($redirectUri);
            }

            return $this->redirectToRoute('waypoint_index');
        }

        return $this->render(
            'waypoint/edit_details.html.twig',
            [
                'form' => $form->createView(),
                'waypoint' => $waypoint,
            ]
        );
    }

    /**
     * @Route("/{id}", name="waypoint_delete", methods={"DELETE"})
     * @IsGranted("ROLE_ADMIN")
     */
    public function delete(Request $request, Waypoint $waypoint): Response
    {
        if ($this->isCsrfTokenValid(
            'delete'.$waypoint->getId(),
            $request->request->get('_token')
        )
        ) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($waypoint);
            $entityManager->flush();
        }

        return $this->redirectToRoute('waypoint_index');
    }

    private function getUploadedFileName($form): ?string
    {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $form['imageFilename']->getData();
        $newFilename = null;

        if ($uploadedFile) {
            $destination = $this->getParameter('kernel.project_dir')
                .'/public/uploads';

            $originalFilename = pathinfo(
                $uploadedFile->getClientOriginalName(),
                PATHINFO_FILENAME
            );
            // $newFilename = Urlizer::urlize($originalFilename).'-'.uniqid().'.'.$uploadedFile->guessExtension();
            $newFilename = $originalFilename.'-'.uniqid('', true).'.'
                .$uploadedFile->guessExtension();
            $uploadedFile->move(
                $destination,
                $newFilename
            );
        }

        return $newFilename;
    }

    /**
     * @Route("/map", name="map-waypoints")
     * @IsGranted("ROLE_ADMIN")
     */
    public function map(): JsonResponse
    {
        $waypoints = $this->getDoctrine()
            ->getRepository(Waypoint::class)
            ->findAll();

        $wps = [];

        foreach ($waypoints as $waypoint) {
            $w = [];

            $w['name'] = $waypoint->getName();
            $w['lat'] = $waypoint->getLat();
            $w['lng'] = $waypoint->getLon();
            $w['id'] = $waypoint->getId();

            $wps[] = $w;
        }

        return $this->json($wps);
    }
}
