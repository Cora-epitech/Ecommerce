<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Common\Annotations\AnnotationReader;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Security\Http\Attribute\CurrentUser;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Mapping\Loader\AnnotationLoader;
use Symfony\Component\Serializer\Mapping\Factory\ClassMetadataFactory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;

class UserController extends AbstractController
{

          
    #[Route('/api/users', name: 'display_user', methods:['GET'])]
    public function allUser(UserRepository $userRepository): JsonResponse
    {
        $encoder = new JsonEncoder();
        $normalizers = new ObjectNormalizer();
        $serializer = new Serializer([$normalizers],[$encoder]);
        $userList = $userRepository->findAll();
        $users  = $serializer->serialize($userList, 'json');

        // Retoure l'objet JSON
        return new JsonResponse($users, Response::HTTP_OK);
    }

    #[Route('/api/register', name:"create_user", methods: ['POST'])]
    public function create_user(Request $request, UserPasswordHasherInterface $passwordHasher,
            EntityManagerInterface $entityManager, UrlGeneratorInterface $urlGenerator,UserRepository $userRepository) {
                $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
                
                $encoder = new JsonEncoder();
                $normalizers = new ObjectNormalizer($classMetadataFactory);
                $serializer = new Serializer([$normalizers],[$encoder]);
                
                // Je reçois une string (getContent()) alors je desérialize pour l'avoir sous forme d'objet
                // J'assigne mon objet user a la classe User
                // Précise que je reçois du JSON
                $user = $serializer->deserialize($request->getContent(),User::class,'json', [
                    AbstractNormalizer::ALLOW_EXTRA_ATTRIBUTES => false,
                ]);

                $plaintextPassword = $user->getPassword();

                // hash the password (based on the security.yaml config for the $user class)
                $hashedPassword = $passwordHasher->hashPassword(
                    $user,
                    $plaintextPassword
                );
                $user->setPassword($hashedPassword);

                var_dump($user);
                $userVerif = $userRepository->findOneBy(array('email' => $user->getEmail()));
                if(!$userVerif){
                    // Avec le manager de Doctrine je persist mon objet user
                    $entityManager->persist($user);
                    // Exécute la requete et envoie TOUT ce qui a été persisté
                    $entityManager->flush();
                    // Il est courant de retourner l'élément qu'on vient d'ajouter pour mieux le retrouver
                    // Je sérialize mon objet pour l'avoir en string
                    // Voir ce que sont les groups ! ! ! 
                    $jsonUser = $serializer->serialize($user, 'json', ['groups'=> 'getUsers']);

                    // je génère l'URL qui va me permettre de récupérer les info du user entré
                    // 'display_one_user' = nom de la route getByid
                    $location = $urlGenerator->generate('display_one_user', ['id' => $user->getId()],UrlGeneratorInterface::ABSOLUTE_URL);

                    // Je retourne un champ de location dans le header de la requête
                
                    return new JsonResponse($jsonUser, Response::HTTP_CREATED,["Location" => $location],true);
                }else{
                    return new JsonResponse(["error" => "Le mail existe déjà"], Response::HTTP_CONFLICT);

                }

    }
    
    #[Route('/api/users/{id}', name: 'display_one_user', methods:['GET'])]
    public function getOneUser(int $id, UserRepository $userRepository) : JsonResponse
    {
        $user = $userRepository->find($id);
        if($user) {
                $classMetadataFactory = new ClassMetadataFactory(new AnnotationLoader(new AnnotationReader()));
                $encoder = new JsonEncoder();
                $normalizers = new ObjectNormalizer($classMetadataFactory);

                $serializer = new Serializer([$normalizers],[$encoder]);
                $jsonUser = $serializer->serialize($user, 'json', ['groups'=> 'getUsers']);
                return new JsonResponse($jsonUser, Response::HTTP_OK);
        }
        return new JsonResponse(null, Response::HTTP_NOT_FOUND);
    }


    #[Route('/api/users/{id}', name:"update_user", methods:['PUT'])]
    public function updateUser(int $id, Request $request, User $currentUser, EntityManagerInterface $entityManger, UserRepository $userRepository): JsonResponse 
    {
        $encoder = new JsonEncoder();
        $normalizers = new ObjectNormalizer();
        $serializer = new Serializer([$normalizers],[$encoder]);

        $updateUser = $serializer->deserialize($request->getContent(), 
                User::class, 
                'json', 
                [AbstractNormalizer::OBJECT_TO_POPULATE => $currentUser]);

        $user = $userRepository->find($id);
        $user->setLogin($currentUser->getLogin());
        $user->setPassword($currentUser->getPassword());
        $user->setEmail($currentUser->getEmail());
        $user->setFirstname($currentUser->getFirstname());
        $user->setLastname($currentUser->getLastname());
        
        $entityManger->persist($user);
        $entityManger->flush();
        return new JsonResponse(null, JsonResponse::HTTP_NO_CONTENT);
   }
}
