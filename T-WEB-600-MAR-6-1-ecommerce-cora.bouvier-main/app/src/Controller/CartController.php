<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Repository\CartRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;

class CartController extends AbstractController
{
    #[Route('/api/carts/{id}', name: 'add_Product', methods:['POST'])]
    public function create_cart(int $id,UserRepository $userRepository,EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator) {

        $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
        
        $encoder = new JsonEncoder();
        $normalizers = new ObjectNormalizer($classMetadataFactory);
        $serializer = new Serializer([$normalizers],[$encoder]);
        
        $user = $userRepository->find($id);

        $cart = new Cart();
        $cart->setUser($user);
        $cart->setIdCart(1);
        $cart->setIdProductsCart("$id;");
        $cart->setTotalPrice(12.3);
        $cart->setStatus(false);
        $cart->setCreatedDate("");

            // Avec le manager de Doctrine je persist mon objet product
            $entityManager->persist($cart);
            // Exécute la requete et envoie TOUT ce qui a été persisté
            $entityManager->flush();

            $jsonProduct = $serializer->serialize($cart, 'json', ['groups'=> 'getCarts']);
            // je génère l'URL qui va me permettre de récupérer les info du produit entré
            $location = $urlGenerator->generate('display_one_product', ['id' => $cart->getId()],UrlGeneratorInterface::ABSOLUTE_URL);

            
            return new JsonResponse($jsonProduct, Response::HTTP_CREATED,["Location" => $location],true);
      
}
}