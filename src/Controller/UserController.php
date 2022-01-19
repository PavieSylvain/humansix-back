<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\CivilityRepository;
use App\Repository\UserRepository;
use mysql_xdevapi\Exception;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Encoder\XmlEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Validator\Constraints\DateTime;
use function MongoDB\BSON\toJSON;

class UserController extends ApiController
{
    /**
     * @Route("/user/getUserByEmail", methods={"POST"})
     */
    public function getUserByEmail(Request $request, UserRepository $userRepository): Response
    {
        $request = $this->transformJsonBody($request);
        $email = $request->get('email');

        $encoders = [new XmlEncoder(), new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $serializer = new Serializer($normalizers, $encoders);
        $user = $userRepository->getByEmail($email);
        $json = $serializer->serialize($user,  'json');
        return $this->json($json);
    }

    /**
     * @Route("/register", methods={"POST","HEAD"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $encoder, CivilityRepository $civilityRepository): JsonResponse
    {
        $em = $this->getDoctrine()->getManager();
        $request = $this->transformJsonBody($request);
        $civility = $civilityRepository->find($request->get('civility_id'));

        $user = new User();
        $user->setPassword($encoder->encodePassword($user, $request->get('password')));
        $user->setEmail($request->get('email'));
        $user->setPseudo($request->get('pseudo'));
        $user->setLastname($request->get('lastname'));
        $user->setFirstname($request->get('firstname'));
        $user->setBirthAt(date_create($request->get('birthAt')));
        $user->setCivility($civility);
        $em->persist($user);

        try {
            $em->flush();
            return $this->respondCreated(sprintf('User successfully created'));
        } catch (\Doctrine\DBAL\Exception $e){
            return $this->respondValidationError("Echec de la création de votre compte");
        }

    }
}