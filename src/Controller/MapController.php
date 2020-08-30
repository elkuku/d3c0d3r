<?php

namespace App\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/map")
 * @IsGranted("ROLE_ADMIN")
 */
class MapController extends AbstractController
{
    /**
     * @Route("/", name="map")
     */
    public function map(): Response
    {
        return $this->render('map/index.html.twig');
    }
}
