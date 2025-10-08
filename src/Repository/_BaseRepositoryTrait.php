<?php
namespace App\Repository;

trait _BaseRepositoryTrait {

    public function setBaseCrudParameters($crudParameters, $qb, $setOrderBy = true) {
        $offset = $crudParameters['itemsPerPage']*($crudParameters['page'] - 1);
        $qb ->setMaxResults($crudParameters['itemsPerPage'])
            ->setFirstResult($offset);
        if($setOrderBy) {
            $qb ->orderBy('a.'.$crudParameters['orderBy'], $crudParameters['sort']);
        }
    }

    public function addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields, $joinUser = true) {
        if(isset($crudParameters['searchTerm']) and $crudParameters['searchTerm'] and !is_null($searchableFields)) {
            //ex: a.firstname LIKE :searchTerm OR a.lastname LIKE :searchTerm OR a.email LIKE :searchTerm
            $searchString = "";
            $separator = "";
            $definedJoin = [];
            foreach ($searchableFields as $searchableField) {
                if($searchableField == 'searchOnUser') { //toujours mettre le paramètre searchOnUser en premier [], pour le OR
                    if($joinUser) {
                        $qb->join('a.user', 'user');
                    }
                    if(str_contains($crudParameters['searchTerm'], '@')) {
                        $searchString = 'user.email = :searchTermUser';
                    } else{
                        //bug tiret
                        $crudParameters['searchTerm'] = str_replace('-', ' ', $crudParameters['searchTerm']);
                        $searchString = 'MATCH_AGAINST(user.firstname, user.lastname) AGAINST (:searchTermUser boolean) > 0';
                    }
                    $parameters['searchTermUser'] = $crudParameters['searchTerm'];
                } elseif(is_array($searchableField)) {
                    $termField = $searchableField['field'];
                    if(!in_array($searchableField['alias'], $definedJoin)) {
                        $qb->leftJoin('a.'.$searchableField['alias'], $searchableField['alias']);
                        $definedJoin[] = $searchableField['alias'];
                    }
                    $searchString = $searchString.$separator." ".$searchableField['alias'].".".$termField." LIKE :searchTerm";
                    $parameters['searchTerm'] = "%".$crudParameters['searchTerm']."%";
                } else {
                    $searchString = $searchString.$separator." a.".$searchableField." LIKE :searchTerm";
                    $parameters['searchTerm'] = "%".$crudParameters['searchTerm']."%";
                }
                $separator = " OR ";
            }
            $qb->andWhere($searchString);

        }
        return $parameters;
    }

    public function findListFiltered($crudParameters, $searchableFields = null) {
        $qb = $this->createQueryBuilder('a');
        $this->setBaseCrudParameters($crudParameters, $qb);
        $parameters = [];
        $parameters = $this->addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields);
        $qb->setParameters($parameters);
        return $qb->getQuery()->getResult();
    }


    public function countListFiltered($crudParameters, $searchableFields = null) {
        $qb = $this
            ->createQueryBuilder('a')
            ->select('COUNT(a)');
        $parameters = [];
        $parameters = $this->addFilterOnSearchTerm($qb, $crudParameters, $parameters, $searchableFields);
        $qb->setParameters($parameters);
        return $qb->getQuery()->getSingleScalarResult();
    }
}