<?php

namespace App\Controller;

use App\Entity\Product;
use App\Repository\ProductRepository;
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

class ProductController extends AbstractController
{
    #[Route('/api/products', name: 'display_products', methods:['GET'])]
    public function allProduct(ProductRepository $productRepository): JsonResponse
    {
        $encoder = new JsonEncoder();
        $normalizers = new ObjectNormalizer();
        $serializer = new Serializer([$normalizers],[$encoder]);
        $productList = $productRepository->findAll();
        $products  = $serializer->serialize($productList, 'json');

        return new JsonResponse($products, Response::HTTP_OK);
    }

    #[Route('/api/products', name:"create_product", methods: ['POST'])]
    public function create_product(Request $request, EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator) {

                $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
                
                $encoder = new JsonEncoder();
                $normalizers = new ObjectNormalizer($classMetadataFactory);
                $serializer = new Serializer([$normalizers],[$encoder]);
                
                $product = $serializer->deserialize($request->getContent(),Product::class,'json', [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                ]);

                    // Avec le manager de Doctrine je persist mon objet product
                    $entityManager->persist($product);
                    // Exécute la requete et envoie TOUT ce qui a été persisté
                    $entityManager->flush();

                    $jsonProduct = $serializer->serialize($product, 'json', ['groups'=> 'getProducts']);
                    // je génère l'URL qui va me permettre de récupérer les info du produit entré
                    $location = $urlGenerator->generate('display_one_product', ['id' => $product->getId()],UrlGeneratorInterface::ABSOLUTE_URL);

                    
                    return new JsonResponse($jsonProduct, Response::HTTP_CREATED,["Location" => $location],true);
              
    }

    #[Route('/api/products/{id}', name: 'display_one_product', methods:['GET'])]
    public function getOneProduct(int $id, ProductRepository $productRepository) : JsonResponse
    {
        $product = $productRepository->find($id);
        
        if($product) {
                $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
                $encoder = new JsonEncoder();
                $normalizers = new ObjectNormalizer($classMetadataFactory);

                $serializer = new Serializer([$normalizers],[$encoder]);
                $jsonProduct = $serializer->serialize($product, 'json', ['groups'=> 'getProducts']);
                // echo("hhhhh");
                // var_dump($product);
                return new JsonResponse($jsonProduct, Response::HTTP_OK);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }

    #[Route('/api/products/{id}', name:"update_product", methods:['PUT'])]
    public function updateProduct(int $id, Request $request, Product $currentProduct, EntityManagerInterface $entityManger, ProductRepository $productRepository): JsonResponse 
    {
        $encoder = new JsonEncoder();
        $normalizers = new ObjectNormalizer();
        $serializer = new Serializer([$normalizers],[$encoder]);

        $updateProduct = $serializer->deserialize($request->getContent(), 
                Product::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentProduct]);

        $product = $productRepository->find($id);
        $product->setName($currentProduct->getName());
        $product->setDescription($currentProduct->getDescription());
        $product->setPhoto($currentProduct->getPhoto());
        $product->setPrice($currentProduct->getPrice());
        
        $entityManger->persist($product);
        $entityManger->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }

   #[Route('/api/products/{id}', name: 'delete_product', methods: ['DELETE'])]
   public function deleteProduct(Product $product, EntityManagerInterface $em): JsonResponse 
   {
       $em->remove($product);
       $em->flush();

       return new JsonResponse(null, Response::HTTP_NO_CONTENT);
   }
}