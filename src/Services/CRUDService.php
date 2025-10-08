<?php
namespace App\Services;

use Symfony\Component\HttpFoundation\Request;

class CRUDService {

    public function getListParametersFromRequest(Request $request) {
        $itemsPerPage = 25;
        if(!is_null($request->get('itemsPerPage'))) {
            $itemsPerPage = intval($request->get('itemsPerPage'));
        }
        //safety
        if($itemsPerPage > 100) {
            $itemsPerPage = 100;
        }
        $page = 1;
        if(!is_null($request->get('currentPage'))) {
            $page = intval($request->get('currentPage'));
        }
        $orderBy = 'id';
        if(!is_null($request->get('sortField'))) {
            $orderBy = $request->get('sortField');
        }
        $sort = 'desc';
        if(!is_null($request->get('sort'))) {
            $sort = $request->get('sort');
        }
        //serachTerm
        $searchTerm = false;
        if(!is_null($request->get('searchTerm')) and strlen($request->get('searchTerm')) > 0) {
            $searchTerm = $request->get('searchTerm');
        }
        return [
            'itemsPerPage' => $itemsPerPage,
            'page' => $page,
            'orderBy' => $orderBy,
            'sort' => $sort,
            'searchTerm' => $searchTerm
        ];
    }
}