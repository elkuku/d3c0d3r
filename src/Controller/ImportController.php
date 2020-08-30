<?php

namespace App\Controller;

use App\Entity\Waypoint;
use App\Form\ImportFormType;
use App\Parser\WayPointParser;
use App\Repository\WaypointRepository;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class ImportController extends AbstractController
{
    /**
     * @Route("/import", name="import")
     * @IsGranted("ROLE_ADMIN")
     */
    public function index(
        Request $request,
        WaypointRepository $waypointRepo,
        WayPointParser $wayPointParser
    ): Response {
        $form = $this->createForm(ImportFormType::class);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $result = $wayPointParser->parse($form->getData());
                $count = $this->storeWayPoints($result['waypoints']);
                if ($count) {
                    $this->addFlash('success', $count.' Waypoint(s) imported!');
                } else {
                    $this->addFlash('warning', 'No Waypoints imported!');
                }

                if ($result['errors']) {
                    $this->addFlash('warning', $result['errors']);
                }

                // TODO reactivate redirect
                // return $this->redirectToRoute('default');
            } catch (Exception $exception) {
                $this->addFlash('danger', $exception->getMessage());

                return $this->render(
                    'import/index.html.twig',
                    [
                        'form' => $form->createView(),
                    ]
                );
            }
        }

        return $this->render(
            'import/index.html.twig',
            [
                'form' => $form->createView(),
            ]
        );
    }

    private function storeWayPoints(array $wayPoints): int
    {
        $repository = $this->getDoctrine()
            ->getRepository(Waypoint::class);
        $entityManager = $this->getDoctrine()->getManager();

        $currentWayPoints = $repository->findAll();

        $cnt = 0;

        foreach ($wayPoints as $wayPoint) {
            foreach ($currentWayPoints as $currentWayPoint) {
                if ($wayPoint->getLat() === $currentWayPoint->getLat()
                    && $wayPoint->getLon() === $currentWayPoint->getLon()
                ) {
                    if ($currentWayPoint->getGuid() === $wayPoint->getGuid()) {
                        continue 2;
                    }

                    if (!$currentWayPoint->getGuid() && $wayPoint->getGuid()) {
                        // guid is missing
                        $currentWayPoint->setGuid($wayPoint->getGuid());
                        $entityManager->persist($currentWayPoint);
                    }

                    continue 2;
                }
            }

            $entityManager->persist($wayPoint);

            $cnt++;
        }

        $entityManager->flush();

        return $cnt;
    }
}
