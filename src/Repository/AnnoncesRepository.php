<?php

namespace App\Repository;

use App\Entity\Annonces;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;

/**
 * @method Annonces|null find($id, $lockMode = null, $lockVersion = null)
 * @method Annonces|null findOneBy(array $criteria, array $orderBy = null)
 * @method Annonces[]    findAll()
 * @method Annonces[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class AnnoncesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Annonces::class);
    }

 /**
  * Undocumented function
  *
  * @param $mots
  * @param $categorie
  * @return void
  */
    public function search($mots = null, $categorie = null)
    {
        $query = $this->createQueryBuilder('a');
        $query->where('a.active = true');

        // traitement des chaînes à rechercher
        if($mots != null){
            // on recherche dans le titre et le ontenu, la chaîne
            $query->andWhere('MATCH_AGAINST(a.title, a.content) AGAINST(:mots boolean) >0')
                    ->setParameter('mots', $mots);
        }

        // traitement de la catégorie à chercher
        if($categorie != null){
            $query->leftJoin('a.categories', 'c');
            $query->andWhere('c.id = :id')
                ->setParameter(':id', $categorie );
        }

        return $query->getQuery()->getResult();

    }

    /**
     * Retourne le nombre d'annonces par jour
     *
     * @return void
     */
    public function countByDate()
    {
        $query = $this->createQueryBuilder('a')
            // prend la chaine date en gardant seulement le jour
            ->select('SUBSTRING(a.createdAt, 1, 10) as datesAnnonces, COUNT(a) as count')
            ->groupBy('datesAnnonces')
        ;

        return $query->getQuery()->getResult();

    }

    /**
     * Retourne annonces entre 2 dates avec des categories
     *
     * @param [type] $from
     * @param [type] $to
     * @return void
     */
    public function searchByInterval($from, $to, $categorie = null)
    {
        $query = $this->createQueryBuilder('a')
         
            ->where('a.createdAt > :from')
            ->andWhere('a.createdAt < :to')
            ->setParameter(':from', $from)
            ->setParameter(':to', $to)
            ;
        if( $categorie != null){
            $query->leftJoin('a.categories', 'c')
                ->andWhere('c.id = :cat')
                ->setParameter(':cat', $categorie)
            ;
        }
        ;

        return $query->getQuery()->getResult();

        /**
         * $query = $this->getEntityManager()->createQuery("SELECT a FROM App\Entity\Annonces * a WHERE a.creataedAt > :from AND a.createdat < :to ")
         * ->setParameter(':from', $from)
         * ->setParameter(':to', $to)
         * 
         * return $query->getResult();
         */

    }

    /**
     * Permet de retourner la pagination des pages à afficher
     *
     * @param  $page
     * @param  $limit
     * @return void
     */
    public function getPaginatedAnnonces($page, $limit)
    {
        $query = $this->createQueryBuilder('a')
         ->where('a.active = 1')
         ->orderBy('a.createdAt')
         ->setFirstResult(($page * $limit) - $limit)   // Offset = décalage 1er élément
         ->setMaxResults($limit)                        // max à afficher
        ;
        return $query->getQuery()->getResult();
    }

    public function getTotalAnnonces(){
        $query = $this->createQueryBuilder('a')
        ->select('COUNT(a)')
        ->where('a.active = 1')
        ;
        // getSingleScalarResult() pour éviter d'avoir un tableau en retour
        return $query->getQuery()->getSingleScalarResult();
    }

    // /**
    //  * @return Annonces[] Returns an array of Annonces objects
    //  */
    /*
    public function findByExampleField($value)
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
        ;
    }
    */

    /*
    public function findOneBySomeField($value): ?Annonces
    {
        return $this->createQueryBuilder('a')
            ->andWhere('a.exampleField = :val')
            ->setParameter('val', $value)
            ->getQuery()
            ->getOneOrNullResult()
        ;
    }
    */
}
