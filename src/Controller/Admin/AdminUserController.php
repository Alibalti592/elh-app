<?php
namespace App\Controller\Admin;

use App\Entity\Pompe;
use App\Entity\RefreshToken;
use App\Entity\User;
use App\Services\CRUDService;
use App\Services\UserService;
use App\UIBuilder\UserUI;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class AdminUserController extends AbstractController
{
    public function __construct(private readonly EntityManagerInterface $entityManager,private readonly UserUI $userUI,
                                private readonly CRUDService $CRUDService, private readonly UserService $userService) {
    }

    #[Route('/list-users', name: 'admin_user_list')]
    public function index(): Response
    {
        $now = new \DateTimeImmutable('now');
        $monthStart = $now->modify('first day of this month')->setTime(0, 0, 0);
        $previousMonthStart = $now->modify('first day of last month')->setTime(0, 0, 0);
        $previousMonthEnd = $now->modify('last day of last month')->setTime(23, 59, 59);
        $yearStart = $now->setDate((int) $now->format('Y'), 1, 1)->setTime(0, 0, 0);

        $countUsers = $this->entityManager->getRepository(User::class)->countUsers();
        $countUsersOfMonth = $this->entityManager
            ->getRepository(User::class)
            ->countUsersBetweenDates($monthStart, $now);
        $countUsersOfPreviousMonth = $this->entityManager
            ->getRepository(User::class)
            ->countUsersBetweenDates($previousMonthStart, $previousMonthEnd);
        $countUsersOfYear = $this->entityManager
            ->getRepository(User::class)
            ->countUsersBetweenDates($yearStart, $now);

        return $this->render('admin/modules/users/list.twig', [
            'countUsers' => $countUsers,
            'countUsersOfMonth' => $countUsersOfMonth,
            'countUsersOfPreviousMonth' => $countUsersOfPreviousMonth,
            'countUsersOfYear' => $countUsersOfYear,
        ]);
    }

    #[Route('/v-load-list-users')]
    public function loadList(Request $request): Response
    {
        $crudParams = $this->CRUDService->getListParametersFromRequest($request);
        $searchableFields = ['firstname', 'lastname', 'email', 'phone'];
        $users = $this->entityManager->getRepository(User::class)->findListFiltered($crudParams, $searchableFields);
        $count = $this->entityManager->getRepository(User::class)->countListFiltered($crudParams, $searchableFields);
        $userUIs = [];
        /** @var User $user */
        foreach ($users as $user) {
            $userUI = $this->userUI->getUserProfilUI($user);
            $userUI['type'] = "Basic";
            $hasPompe = $this->entityManager->getRepository(Pompe::class)->countManagedPompes($user) > 0;
            if($hasPompe) {
                $userUI['type'] .= " | Gestionnaire PF";
            }
            if(in_array('ROLE_ADMIN', $user->getRoles()))  {
                $userUI['type'] .= " | Admin.";
            }
            $userUI['lastActivity'] = !is_null($user->getLastLogin())
                ? $user->getLastLogin()->format('d/m/Y H:i')
                : '-';
            $userUI['enabled'] = $user->isEnabled();
            $userUIs[] = $userUI;
        }
        $jsonResponse = new JsonResponse();
        $jsonResponse->setData([
            'users' => $userUIs,
            'count' => $count
        ]);
        return $jsonResponse;
    }
    #[Route('/v-admin-user-updatepassword')]
    public function updatePassword(Request $request, UserPasswordHasherInterface $userPasswordHasher)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'id' => $request->get('user')
        ]);
        $jsonResponse = new JsonResponse();
        if(!is_null($request->get('newPassword')) && strlen($request->get('newPassword')) > 5) {
            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get('newPassword')
                )
            );
        } else {
            $jsonResponse->setStatusCode(500);
            $jsonResponse->setData(['message' => 'Le mot de passe doit faire plus de 5 caractères']);
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $jsonResponse;
    }


    #[Route('/v-admin-user-block')]
    public function blockUser(Request $request)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'id' => $request->get('user')
        ]);
        $jsonResponse = new JsonResponse();
        if($user->isEnabled()) {
            //remove refresh tokens
            $tokens = $this->entityManager->getRepository(RefreshToken::class)->findBy([
                'username' => $user->getEmail()
            ]);
            foreach ($tokens as $token) {
                $this->entityManager->remove($token);
            }
            $this->entityManager->flush();
            $user->setEnabled(false);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        } else {
            $user->setEnabled(true);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        $this->entityManager->persist($user);
        $this->entityManager->flush();

        return $jsonResponse;
    }

    #[Route('/v-admin-user-delete')]
    public function deleteUser(Request $request)
    {
        $user = $this->entityManager->getRepository(User::class)->findOneBy([
            'id' => $request->get('user')
        ]);
        $jsonResponse = new JsonResponse();
        $this->userService->deleteUserAccount($user);
        return $jsonResponse;
    }

    #[Route('/export-csv-users', name: 'admin_user_export_csv')]
    public function exportCsv(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();

        $response = new StreamedResponse(function () use ($users) {
            $handle = fopen('php://output', 'w');
            // Add UTF-8 BOM for Excel compatibility
            fprintf($handle, chr(0xEF).chr(0xBB).chr(0xBF));
            
            fputcsv($handle, ['ID', 'Prénom', 'Nom', 'Email', 'Téléphone', 'Date d\'inscription', 'Dernière connexion', 'Statut', 'Activé'], ';');

            foreach ($users as $user) {
                fputcsv($handle, [
                    $user->getId(),
                    $user->getFirstname(),
                    $user->getLastname(),
                    $user->getEmail(),
                    $user->getPhone(),
                    $user->getCreateAt() ? $user->getCreateAt()->format('d/m/Y H:i') : '-',
                    $user->getLastLogin() ? $user->getLastLogin()->format('d/m/Y H:i') : '-',
                    $user->getStatus(),
                    $user->isEnabled() ? 'Oui' : 'Non',
                ], ';');
            }
            fclose($handle);
        });

        $response->headers->set('Content-Type', 'text/csv');
        $response->headers->set('Content-Disposition', 'attachment; filename="users_export.csv"');

        return $response;
    }
}
