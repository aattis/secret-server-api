<?php

namespace App\Controller;

use App\Entity\Secret;

use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;


class SecretController extends AbstractController
{
    
    #[Route('v1/secret', methods: ['POST'], name: 'create_secret')]
    public function createSecret(ManagerRegistry $doctrine, Request $request): Response
    {
        $entityManager = $doctrine->getManager();
        
        $secret = new Secret();
        $secret->setSecret($request->get('secret'));
        $secret->setExpireAfterViews($request->get('expireAfterViews'));
        $secret->setExpireAfter($request->get('expireAfter'));
        $secret->setHash(md5(rand(10,1000).$request->get('secret')));
      
        $entityManager->persist($secret);
        $entityManager->flush();

        $thesecret = $doctrine->getRepository(Secret::class)->find($secret->getId());

        $header = $request->headers->get('accept');
        if($header == "application/json"){
            return new JsonResponse($thesecret->createResponseFromSecret());
        } elseif ($header == "application/xml"){
            return new Response($thesecret->createSecretXMLResponse());
        }

        return new Response('Invalid input', 405);
    }

    #[Route('v1/secret/{hash}', methods: ['GET'], name: 'show_secret')]
    public function showSecret(ManagerRegistry $doctrine, string $hash, Request $request)
    {
        if (!$hash || $hash == null) {
            return new Response('Secret not found (Missing hash code)', 404);           
        }

        $thesecret = $doctrine->getRepository(Secret::class)->findOneBy(['hash' => $hash]);

        if(!$thesecret || 
            $thesecret->getExpireAfterViews() < 1 ||
            $thesecret->isExpired()){
            return new Response('Secret not found (No secret with this hash or secret expired)'.$thesecret->isExpired(), 404); 
        }

        $entityManager = $doctrine->getManager();

        //megjelenítés után a további megtekintések számának csökkentése
        $rv = $thesecret->getExpireAfterViews();
        $thesecret->setExpireAfterViews(--$rv);
        
        $entityManager->persist($thesecret);
        $entityManager->flush();

        $header = $request->headers->get('accept');
        if($header == "application/json"){
            return new JsonResponse($thesecret->createResponseFromSecret());
        } elseif ($header == "application/xml"){
            return new Response($thesecret->createSecretXMLResponse());
        }

        return new Response('Invalid input', 405);
    }
}