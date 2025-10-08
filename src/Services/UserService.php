<?php
namespace App\Services;


use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class UserService
{
    public function __construct(private readonly EntityManagerInterface $entityManager, private readonly S3Service $s3Service){}

    public function deleteUserAccount(User $user)  {
        $userMail = $user->getEmail();
        $image = $user->getPhoto();
        if(!is_null($image)) {
            $this->s3Service->deleteFileFromMedia($image);
            $user->setPhoto(null);
            $this->entityManager->remove($image);
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }
        $user->setEmail('deleteeed-'.$userMail);
        $user->setPhone(null);
        $user->setEnabled(false);
        $user->setDeletedAt(new \DateTimeImmutable('now'));
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        //del dette / testatement  ... ?
    }
}