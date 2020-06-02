<?php

namespace App\Controller;

use App\Repository\OpinionRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class OpinionController extends AbstractController
{
    /**
     * @Route("/opinion/{id}", name="opinion_delete")
     */
    public function opinion_delete($id, OpinionRepository $opinionRepository)
    {
        $manager = $this->getDoctrine()->getManager();
        $opinion = $opinionRepository->find($id);

        if ($opinion == null) {
            return $this->redirectToRoute('index');
        } else if ($opinion->getUser()->getUser() != $this->getUser()) {
            return $this->redirectToRoute('index');
        }

        $manager->remove($opinion);
        $manager->flush();

        $this->addFlash('success', 'Votre commentaire à bien été supprimé.');
        return $this->redirectToRoute('index');
    }
}
