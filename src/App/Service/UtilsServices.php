<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 02.12.16
 * Time: 09:16
 */
namespace App\Service;



class UtilsServices
{
    public function __construct($container)
    {
        //$this->mailService = $container['mailServices'];
        $this->membershipServices = $container->get('membershipServices');
        $this->em = $container['em'];

        $repository = $this->em->getRepository('App\Entity\Settings');
        $this->settings = $repository->createQueryBuilder('settings')
            ->select('settings')
            ->getQuery()
            ->getOneOrNullResult();
    }

    //implements binary search to find membership by ownerId in array: ARRAY MUST BE SORTED BY ownerId !!!
    public function searchMembershipsByOwnerId($sortedArray, $id)
    {
        $l = 0;
        $r = count($sortedArray) - 1;

        while ($l <= $r){

            $m = round(($l + $r)/ 2);
            if ($sortedArray[$m]->getOwnerId() < $id){
                $l = $m +1;
            }
            elseif ($sortedArray[$m]->getOwnerId() > $id){
                $r = $m - 1;
            }
            elseif ($sortedArray[$m]->getOwnerId() == $id){

                return $sortedArray[$m];
            }
        }
        return null;
    }

    public function processFilterForMembersTable($filter_form)
    {

        //sanitize post variables, just to be sure
        $typeAndGradeFilter = array();
        if (isset($filter_form['membershipTypeId']) AND $filter_form['membershipTypeId'] != -1){
            $typeAndGradeFilter['membershipTypeId'] = (int)$filter_form['membershipTypeId'];
        }
        if (isset($filter_form['membershipGrade']) AND $filter_form['membershipGrade'] != -1){
            $typeAndGradeFilter['membershipGrade'] = (int)$filter_form['membershipGrade'];
        }

        if (!isset($filter_form['validity'])){
            $validity = -1;
        }
        else{
            $validity = $filter_form['validity'];
        }

        $now = new \DateTime();
        $current_year = $now->format('Y');
        
        switch ($validity){
            case null:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = null;
                break;
            case -1:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = null;
                break;
            case 0:
                $validity_filter['onlyValid'] = true;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = null;
                break;
            case 1:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = new \DateTime($current_year.'-12-31T23:59:59');
                break;
            case 2:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = new \DateTime($current_year.'-12-31T23:59:59');
                $validity_filter['validity']->add(new \DateInterval('P1Y'));
                break;
            case 3:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = new \DateTime($current_year.'-12-31T23:59:59');
                $validity_filter['validity']->sub(new \DateInterval('P1Y'));
                break;
            case 4:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = new \DateTime($current_year.'-12-31T23:59:59');
                $validity_filter['validity']->sub(new \DateInterval('P2Y'));
                break;
            case 5:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = true;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = null;
                break;
            case 6:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = true;
                $validity_filter['validity'] = null;
                break;
            default:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = null;
                break;
        }

        // get data to generate the filter options

        //Currently only YEAR is supported
        $recurrence = 'year';
        switch ($recurrence){

            case 'year':
                $now = new \DateTime();
                $current_year = $now->format('Y');
                $current_period = new \DateTime($current_year.'-12-31T23:59:59');
                $validity_form['current_period'] = $current_period->format('jS F Y');
                $next_period = $current_period->add(new \DateInterval('P1Y'));
                $validity_form['next_period'] = $next_period->format('jS F Y');
                $one_period_ago = $current_period->sub(new \DateInterval('P2Y'));
                $validity_form['one_period_ago'] = $one_period_ago->format('jS F Y');
                $two_periods_ago = $current_period->sub(new \DateInterval('P1Y'));
                $validity_form['two_periods_ago'] = $two_periods_ago->format('jS F Y');
                break;
        }

        $membershipTypesResp = $this->membershipServices->getAllMembershipTypes();
        $memberGradesResp = $this->membershipServices->getAllMemberGrades();

        return array('exception' => false,
                     'ValidityFilter' => $validity_filter,
                     'typeAndGradeFilter' => $typeAndGradeFilter,
                     'filter_form' => array('membershipTypes' => $membershipTypesResp['membershipTypes'],
                                            'memberGrades' => $memberGradesResp['memberGrades'],
                                            'validity' => $validity_form));
    }



}