<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\RegistrationFormType;
use App\Form\LoginFormType;
use Doctrine\ORM\EntityManagerInterface;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use Symfony\Component\Security\Http\Authentication\AuthenticationUtils; 
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAuthenticationException;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;

use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;

class SecurityController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, EntityManagerInterface $entityManager): Response
    {
        $user = new User();
        $form = $this->createForm(RegistrationFormType::class, $user);
        $form->handleRequest($request);
    
        if ($form->isSubmitted() && $form->isValid()) {
          
            $existingUser = $entityManager->getRepository(User::class)->findOneBy(['email' => $user->getEmail()]);
            if ($existingUser) {
                $form->get('email')->addError(new FormError('This email is already used.'));
                return $this->render('security/register.html.twig', [
                    'registrationForm' => $form->createView(),
                ]);
            }
    
           
            $plainPassword = $form->get('plainPassword')->getData();
            $user->setPassword($plainPassword);
            $user->setRoles(['ROLE_USER']); 
         
            $entityManager->persist($user);
            $entityManager->flush();
    
           
            return $this->redirectToRoute('app_login');
        }
    
        return $this->render('security/register.html.twig', [
            'registrationForm' => $form->createView(),
        ]);
    }
    
   
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


  public function login(Request $request, AuthenticationUtils $authenticationUtils): Response
  {
      
    $error = $authenticationUtils->getLastAuthenticationError();
    $lastUsername = $authenticationUtils->getLastUsername();

    $form = $this->createForm(LoginFormType::class);

    if ($request->isMethod('POST')) {
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
         
            $data = $form->getData();
            $username = $data['username'];
            $password = $data['password'];
            try {
               
                $user = $this->entityManager->getRepository(User::class)->findOneBy(['email' => $username]);
                if ($user) {
                   
                    if ($user->getIsBlocked()) {
                        throw new CustomUserMessageAuthenticationException('Your account is blocked. Please contact the administrator.');
                    }
                    $authenticatedUser = $this->customAuthenticate($username, $password);
                    if ($authenticatedUser) {
                        
                        $session = $request->getSession();
                        $session->set('user', [
                            'id' => $user->getId(),
                            'email' => $user->getEmail(),
                            'roles' => $user->getRoles(),
                        ]);

                        if (in_array('ROLE_ADMIN', $user->getRoles())) {
                           
                            return $this->redirectToRoute('admin_dashboard');
                        }   
                        return $this->redirectToRoute('index');
                    } else {
                       
                        throw new CustomUserMessageAuthenticationException('Invalid password.');
                    }
                } else {
                   
                    throw new CustomUserMessageAuthenticationException('User does not exist.');
                }
            } catch (CustomUserMessageAuthenticationException $e) {
              
                $error = $e;
            }
        }
    }

   
    return $this->render('security/login.html.twig', [
        'last_username' => $lastUsername,
        'error' => $error,
        'form' => $form->createView(),
    ]);
  }

private function customAuthenticate(string $username, string $password): ?User
{
  
    $userRepository = $this->entityManager->getRepository(User::class);
    $user = $userRepository->findOneBy(['email' => $username]);

    if ($user && $user->getPassword() === $password) {
        return $user;
    } else {
       
        return null;
    }
}

   


}