<?php

namespace App\Controller;

use App\Entity\Waypoint;
use App\Entity\WaypointReference;
use App\Service\UploaderHelper;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\HeaderUtils;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Constraints\File;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class WaypointReferenceController extends AbstractController
{
    /**
     * @Route("/admin/waypoint/{id}/references", name="admin_waypoint_add_reference", methods={"POST"})
     */
    public function uploadArticleReference(
        Waypoint $waypoint,
        Request $request,
        UploaderHelper $uploaderHelper,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator
    ) {
        /** @var UploadedFile $uploadedFile */
        $uploadedFile = $request->files->get('reference');

        // dump($uploadedFile);

        $violations = $validator->validate(
            $uploadedFile,
            [
                new NotBlank(),
                new File(
                    [
                        'maxSize' => '5M',
                        'mimeTypes' => [
                            'image/*',
                            'application/pdf',
                            'application/msword',
                            'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
                            'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
                            'application/vnd.openxmlformats-officedocument.presentationml.presentation',
                            'text/plain',
                        ],
                    ]
                ),
            ]
        );

        if ($violations->count() > 0) {
            return $this->json($violations, 400);
            // /** @var ConstraintViolation $violation */
            // $violation = $violations[0];
            // $this->addFlash('error', $violation->getMessage());
            //
            // return $this->redirectToRoute(
            //     'waypoint_edit',
            //     [
            //         'id' => $waypoint->getId(),
            //     ]
            // );
        }

        $filename = $uploaderHelper->uploadArticleReference($uploadedFile);

        $reference = new WaypointReference($waypoint);
        $reference->setFilename($filename);
        $reference->setOriginalFilename(
            $uploadedFile->getClientOriginalName() ?? $filename
        );
        $reference->setMimeType(
            $uploadedFile->getMimeType() ?? 'application/octet-stream'
        );

        $entityManager->persist($reference);
        $entityManager->flush();

        return $this->json(
            $reference,
            201,
            [],
            [
                'groups' => ['main'],
            ]
        );

        // return $this->json($reference);

        // return $this->redirectToRoute(
        //     'waypoint_edit',
        //     [
        //         'id' => $waypoint->getId(),
        //     ]
        // );
    }

    /**
     * @Route("/waypoint/references/{id}/download", name="admin_waypoint_download_reference", methods={"GET"})
     */
    public function downloadWaypointReference(
        WaypointReference $reference,
        UploaderHelper $uploaderHelper
    ) {
        // $article = $reference->getWaypoint();
        // $this->denyAccessUnlessGranted('MANAGE', $article);
        $response = new StreamedResponse(
            function () use ($reference, $uploaderHelper) {
                $outputStream = fopen('php://output', 'wb');
                $fileStream = $uploaderHelper->readStream(
                    $reference->getFilePath(),
                    false
                );
                stream_copy_to_stream($fileStream, $outputStream);
            }
        );

        $response->headers->set('Content-Type', $reference->getMimeType());

        $disposition = HeaderUtils::makeDisposition(
            HeaderUtils::DISPOSITION_ATTACHMENT,
            $reference->getOriginalFilename()
        );

        $response->headers->set('Content-Disposition', $disposition);

        // dd($disposition);

        return $response;
    }

    /**
     * @Route("/waypoint/{id}/references", methods="GET", name="admin_article_list_references")
     */
    public function getWaypointReferences(Waypoint $waypoint)
    {
        return $this->json(
            $waypoint->getWaypointReferences(),
            200,
            [],
            [
                'groups' => ['main'],
            ]
        );

        return $this->json($waypoint->getWaypointReferences());
    }

    /**
     * @Route("/waypoint/references/{id}", name="admin_waypoint_delete_reference", methods={"DELETE"})
     */
    public function deleteWaypointReference(
        WaypointReference $reference,
        UploaderHelper $uploaderHelper,
        EntityManagerInterface $entityManager
    ): Response {
        // $article = $reference->getArticle();
        // $this->denyAccessUnlessGranted('MANAGE', $article);

        $entityManager->remove($reference);
        $entityManager->flush();
        $uploaderHelper->deleteFile($reference->getFilePath(), false);

        return new Response(null, 204);
    }

    /**
     * @Route("/waypoint/references/{id}", name="admin_article_update_reference", methods={"PUT"})
     */
    public function updateWaypointReference(
        WaypointReference $reference,
        UploaderHelper $uploaderHelper,
        EntityManagerInterface $entityManager,
        SerializerInterface $serializer,
        Request $request,
        ValidatorInterface $validator
    ) {
        // $article = $reference->getWaypoint();
        // $this->denyAccessUnlessGranted('MANAGE', $article);

        $serializer->deserialize(
            $request->getContent(),
            WaypointReference::class,
            'json',
            [
                'object_to_populate' => $reference,
                'groups'             => ['input'],
            ]
        );

        $violations = $validator->validate($reference);

        if ($violations->count() > 0) {
            return $this->json($violations, 400);
        }

        $entityManager->persist($reference);
        $entityManager->flush();

        return $this->json(
            $reference,
            200,
            [],
            [
                'groups' => ['main'],
            ]
        );
    }

    /**
     * @Route("/waypoint/{id}/references/reorder", methods="POST", name="admin_waypoint_reorder_references")
     * IsGranted("MANAGE", subject="article")
     */
    public function reorderWaypointReferences(
        Waypoint $waypoint,
        Request $request,
        EntityManagerInterface $entityManager
    ) {
        $orderedIds = json_decode($request->getContent(), true);

        if ($orderedIds === null) {
            return $this->json(['detail' => 'Invalid body'], 400);
        }

        // from (position)=>(id) to (id)=>(position)
        $orderedIds = array_flip($orderedIds);

        foreach ($waypoint->getWaypointReferences() as $reference) {
            $reference->setPosition($orderedIds[$reference->getId()]);
        }

        $entityManager->flush();

        return $this->json(
            $waypoint->getWaypointReferences(),
            200,
            [],
            [
                'groups' => ['main'],
            ]
        );
    }
}
