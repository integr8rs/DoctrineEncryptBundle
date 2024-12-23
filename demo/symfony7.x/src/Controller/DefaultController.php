<?php

namespace DoctrineEncryptBundle\Demo\Symfony7x\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class DefaultController extends AbstractController
{
    #[Route(name: 'home', path: '/')]
    public function index(\DoctrineEncryptBundle\Demo\Symfony7x\Repository\SecretRepository $secretUsingAttributesRepository): Response
    {
        $secrets = $secretUsingAttributesRepository->findAll();

        return $this->render('index.html.twig', ['secrets' => $secrets]);
    }

    #[Route(name: 'create', path: '/create')]
    public function create(Request $request, EntityManagerInterface $em): Response
    {
        if (!$request->query->has('name') || !$request->query->has('secret') || !$request->query->has('type')) {
            return new Response('Please specify name, secret and type in url-query');
        }

        $type = $request->query->get('type');
        if ($type === 'attribute') {
            $secret = new \DoctrineEncryptBundle\Demo\Symfony7x\Entity\Secret();
        } else {
            return new Response('Type is only allowed to be "attribute"');
        }

        $secret
            ->setName($request->query->getAlnum('name'))
            ->setSecret($request->query->getAlnum('secret'));

        $em->persist($secret);
        $em->flush();

        return new Response(sprintf('OK - secret %s stored', $secret->getName()));
    }
}
