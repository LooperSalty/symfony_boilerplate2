<?php

namespace App\Controller;

use App\Entity\Client;
use App\Form\ClientType;
use App\Repository\ClientRepository;
use App\Security\Voter\ClientVoter;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/clients')]
class ClientController extends AbstractController
{
    public function __construct(
        private EntityManagerInterface $entityManager
    ) {
    }

    #[Route('', name: 'app_client_index', methods: ['GET'])]
    public function index(ClientRepository $clientRepository): Response
    {
        $this->denyAccessUnlessGranted(ClientVoter::LIST);

        return $this->render('client/index.html.twig', [
            'clients' => $clientRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_client_new', methods: ['GET', 'POST'])]
    public function new(Request $request): Response
    {
        $this->denyAccessUnlessGranted(ClientVoter::CREATE);

        $client = new Client();
        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->persist($client);
            $this->entityManager->flush();

            $this->addFlash('success', 'Client créé avec succès.');
            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('client/form.html.twig', [
            'form' => $form,
            'client' => $client,
            'is_edit' => false,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_client_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Client $client): Response
    {
        $this->denyAccessUnlessGranted(ClientVoter::EDIT, $client);

        $form = $this->createForm(ClientType::class, $client);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->entityManager->flush();

            $this->addFlash('success', 'Client modifié avec succès.');
            return $this->redirectToRoute('app_client_index');
        }

        return $this->render('client/form.html.twig', [
            'form' => $form,
            'client' => $client,
            'is_edit' => true,
        ]);
    }

    #[Route('/{id}/delete', name: 'app_client_delete', methods: ['POST'])]
    public function delete(Request $request, Client $client): Response
    {
        $this->denyAccessUnlessGranted(ClientVoter::DELETE, $client);

        if ($this->isCsrfTokenValid('delete'.$client->getId(), $request->request->get('_token'))) {
            $this->entityManager->remove($client);
            $this->entityManager->flush();

            $this->addFlash('success', 'Client supprimé avec succès.');
        }

        return $this->redirectToRoute('app_client_index');
    }
}
