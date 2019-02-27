<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Query;
use Symfony\Bridge\Doctrine\RegistryInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

/**
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository
{
    private $passwordEncoder;

    public function __construct(RegistryInterface $registry, UserPasswordEncoderInterface $passwordEncoder)
    {

        $this->passwordEncoder = $passwordEncoder;
        parent::__construct($registry, User::class);

    }


    // /**
    //  * @return User[] Returns an array of User objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('u')
            ->andWhere('u.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('u.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */


    /**
     * @param string $id
     * @return mixed
     */
    public function findById(string $id)
   {
        return $this->createQueryBuilder('u')
           ->andWhere('u.id = :id')
            ->setParameter('id', $id)
           ->getQuery()
            ->getOneOrNullResult(Query::HYDRATE_SIMPLEOBJECT)
        ;
   }


    /**
     * @param $username
     * @param $password
     */
    public function createAdminFromCommand($username, $plainPassword){

        $role [] =  "ROLE_USER";
        $admin = new User();
        $adminLn = count($this->findAll());
        if($adminLn < 1) {
            $admin->setEmail($username);
            $admin->setRoles($role);
            $admin->setNom('Jean');
            $admin->setPrenom('Vincent');
            $admin->setNomEntreprise('Blabla');
            $admin->setLogo('blabla.png');
            $admin->setCodePostal('69440');
            $admin->setVille('lulala');
            $admin->setAdresse('lulali');
            $admin->setSiteWeb('siteWeb.fr');
            $admin->setSocial('facebook');
            $admin->setTelephone('0654875421');

            $encodedPassword = $this->passwordEncoder->encodePassword($admin, $plainPassword);
            $admin->setPassword($encodedPassword);

            $this->getEntityManager()->persist($admin);
            $this->getEntityManager()->flush();
        }else{
                throw new \RuntimeException("Vous ne pouvez pas rajouter un user \n
            pour remplacer l'utilisateur,  veuillez vous connecter à phpmyadmin et le supprimer\n
            puis rejouez la commande");
            }

    }
}
