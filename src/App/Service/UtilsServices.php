<?php
/**
 * Created by PhpStorm.
 * User: garbi
 * Date: 02.12.16
 * Time: 09:16
 */
namespace App\Service;

use \Httpful\Request;


class UtilsServices
{
    public function __construct($container)
    {
        //$this->mailService = $container['mailServices'];
        $this->membershipServices = $container->get('membershipServices');
        $this->container = $container;
       // $this->userServices = $container->get('userServices');
        $this->em = $container['em'];

        $repository = $this->em->getRepository('App\Entity\Settings');
        $this->settings = $repository->createQueryBuilder('settings')
            ->select('settings')
            ->getQuery()
            ->getOneOrNullResult();
    }

    public function verifyRecaptcha($response, $remoteip)
    {
        $url = "https://www.google.com/recaptcha/api/siteverify";
        $secret = "6LdDk1sUAAAAAJuD4KhSUZOKvuhVTFJb6mMUuAnT";

        $url = $this->settings->getReCaptchaURL();
        $secret = $this->settings->getReCaptchaSecretKey();

        $req = "secret=$secret";
        $req .= "&response=$response";
        $req .= "&remoteip=$remoteip";

        //var_dump($req); "Content-Type", "application/x-www-form-urlencoded; charset=utf-8"

        try{
            $response= Request::post($url)
                ->addHeader('Content-Type','application/x-www-form-urlencoded; charset=utf-8')
                ->body($req)
                ->send();
        }
        catch (Exception $e) {
            return array('exception' => true,
                'verified' => false,
                'message' => $e->getMessage());
        }

        return $response->body;

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

    public function processFilterForMembersTable($filter_form, $userId = null)
    {

        //sanitize post variables, just to be sure
        $membership_filter = array();
        if (isset($filter_form['membershipTypeId']) AND $filter_form['membershipTypeId'] != -1){
            $membership_filter['membershipTypeId'] = (int)$filter_form['membershipTypeId'];
        }
        if (isset($filter_form['membershipGrade']) AND $filter_form['membershipGrade'] != -1){
            $membership_filter['membershipGrade'] = (int)$filter_form['membershipGrade'];
        }

        //get only memberships that have not been cancelled, if not stated otherwise
        $membership_filter['cancelled'] = false;

        $user_filter = array();
        if (isset($filter_form['country']) AND $filter_form['country'] != ''){
            $user_filter['country'] = $filter_form['country'];
        }

        if ($filter_form['newsletterConsent'] == 'on'){
            $user_filter['newsletterConsent'] = true;
        }

        if ($filter_form['membershipEmailConsent'] == 'on'){
            $user_filter['membershipEmailConsent'] = true;
        }

        if ($filter_form['generalTermsConsent'] == 'on'){
            $user_filter['generalTermsConsent'] = true;
        }

        if ($userId != null){
            $user_filter['id'] = $userId;
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
            case 7:
                $validity_filter['onlyValid'] = false;
                $validity_filter['onlyExpired'] = false;
                $validity_filter['never_validated'] = false;
                $validity_filter['validity'] = null;

                $membership_filter['cancelled'] = true;
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
                     'membership_filter' => $membership_filter,
                     'user_filter' => $user_filter,
                     'filter_form' => array('membershipTypes' => $membershipTypesResp['membershipTypes'],
                                            'memberGrades' => $memberGradesResp['memberGrades'],
                                            'validity' => $validity_form));
    }

    public function newMembershipPossible($userId)
    {
        $userServices = $this->container->get('userServices');
        $resUser = $userServices->getUserById($userId);

        if ($resUser['exception'] == true){
            return false;
        }
        $membershipsTypes = $this->membershipServices->getMembershipTypeAndStatusOfUser($resUser['user'], NULL, false);

        if ($membershipsTypes['exception'] == false){

            foreach ($membershipsTypes['membershipTypes'] as $membershipType){

                if ($membershipType['owner'] == false){
                    return true;
                }
            }
            return false;
        }
        return false;
    }

    public function getBaseUrl($request)
    {
        if (isset($_SERVER['HTTPS'])) {

            if ($_SERVER['HTTPS'] != NULL) {

                $baseUrl = $request->getUri()->getScheme() . '://' . $request->getUri()->getHost();
            }
            else {
                $baseUrl = $request->getUri()->getBaseUrl();
            }
        }
        else{
            $baseUrl = $request->getUri()->getBaseUrl();
        }
        return $baseUrl;
    }
    
    public function getCurrentRouteName($request)
    {
        return $request->getAttribute('route')->getName();
    }

    public function getUrlForRouteName($request, $routeName, $params = array())
    {
        return $this->getBaseUrl($request).$this->container->router->pathFor($routeName, $params);
    }

    public function getCurrentUrl($request)
    {
        return  $this->getBaseUrl($request).$request->getUri()->getPath();
    }


    // code should be a string according to ISO 3166-1 alpha-2 schema
    public function getCountryNameByCode($code)
    {
        $countries = array
        (
            'AF' => 'Afghanistan',
            'AX' => 'Aland Islands',
            'AL' => 'Albania',
            'DZ' => 'Algeria',
            'AS' => 'American Samoa',
            'AD' => 'Andorra',
            'AO' => 'Angola',
            'AI' => 'Anguilla',
            'AQ' => 'Antarctica',
            'AG' => 'Antigua And Barbuda',
            'AR' => 'Argentina',
            'AM' => 'Armenia',
            'AW' => 'Aruba',
            'AU' => 'Australia',
            'AT' => 'Austria',
            'AZ' => 'Azerbaijan',
            'BS' => 'Bahamas',
            'BH' => 'Bahrain',
            'BD' => 'Bangladesh',
            'BB' => 'Barbados',
            'BY' => 'Belarus',
            'BE' => 'Belgium',
            'BZ' => 'Belize',
            'BJ' => 'Benin',
            'BM' => 'Bermuda',
            'BT' => 'Bhutan',
            'BO' => 'Bolivia',
            'BA' => 'Bosnia And Herzegovina',
            'BW' => 'Botswana',
            'BV' => 'Bouvet Island',
            'BR' => 'Brazil',
            'IO' => 'British Indian Ocean Territory',
            'BN' => 'Brunei Darussalam',
            'BG' => 'Bulgaria',
            'BF' => 'Burkina Faso',
            'BI' => 'Burundi',
            'KH' => 'Cambodia',
            'CM' => 'Cameroon',
            'CA' => 'Canada',
            'CV' => 'Cape Verde',
            'KY' => 'Cayman Islands',
            'CF' => 'Central African Republic',
            'TD' => 'Chad',
            'CL' => 'Chile',
            'CN' => 'China',
            'CX' => 'Christmas Island',
            'CC' => 'Cocos (Keeling) Islands',
            'CO' => 'Colombia',
            'KM' => 'Comoros',
            'CG' => 'Congo',
            'CD' => 'Congo, Democratic Republic',
            'CK' => 'Cook Islands',
            'CR' => 'Costa Rica',
            'CI' => 'Cote D\'Ivoire',
            'HR' => 'Croatia',
            'CU' => 'Cuba',
            'CY' => 'Cyprus',
            'CZ' => 'Czech Republic',
            'DK' => 'Denmark',
            'DJ' => 'Djibouti',
            'DM' => 'Dominica',
            'DO' => 'Dominican Republic',
            'EC' => 'Ecuador',
            'EG' => 'Egypt',
            'SV' => 'El Salvador',
            'GQ' => 'Equatorial Guinea',
            'ER' => 'Eritrea',
            'EE' => 'Estonia',
            'ET' => 'Ethiopia',
            'FK' => 'Falkland Islands (Malvinas)',
            'FO' => 'Faroe Islands',
            'FJ' => 'Fiji',
            'FI' => 'Finland',
            'FR' => 'France',
            'GF' => 'French Guiana',
            'PF' => 'French Polynesia',
            'TF' => 'French Southern Territories',
            'GA' => 'Gabon',
            'GM' => 'Gambia',
            'GE' => 'Georgia',
            'DE' => 'Germany',
            'GH' => 'Ghana',
            'GI' => 'Gibraltar',
            'GR' => 'Greece',
            'GL' => 'Greenland',
            'GD' => 'Grenada',
            'GP' => 'Guadeloupe',
            'GU' => 'Guam',
            'GT' => 'Guatemala',
            'GG' => 'Guernsey',
            'GN' => 'Guinea',
            'GW' => 'Guinea-Bissau',
            'GY' => 'Guyana',
            'HT' => 'Haiti',
            'HM' => 'Heard Island & Mcdonald Islands',
            'VA' => 'Holy See (Vatican City State)',
            'HN' => 'Honduras',
            'HK' => 'Hong Kong',
            'HU' => 'Hungary',
            'IS' => 'Iceland',
            'IN' => 'India',
            'ID' => 'Indonesia',
            'IR' => 'Iran, Islamic Republic Of',
            'IQ' => 'Iraq',
            'IE' => 'Ireland',
            'IM' => 'Isle Of Man',
            'IL' => 'Israel',
            'IT' => 'Italy',
            'JM' => 'Jamaica',
            'JP' => 'Japan',
            'JE' => 'Jersey',
            'JO' => 'Jordan',
            'KZ' => 'Kazakhstan',
            'KE' => 'Kenya',
            'KI' => 'Kiribati',
            'KR' => 'Korea',
            'KW' => 'Kuwait',
            'KG' => 'Kyrgyzstan',
            'LA' => 'Lao People\'s Democratic Republic',
            'LV' => 'Latvia',
            'LB' => 'Lebanon',
            'LS' => 'Lesotho',
            'LR' => 'Liberia',
            'LY' => 'Libyan Arab Jamahiriya',
            'LI' => 'Liechtenstein',
            'LT' => 'Lithuania',
            'LU' => 'Luxembourg',
            'MO' => 'Macao',
            'MK' => 'Macedonia',
            'MG' => 'Madagascar',
            'MW' => 'Malawi',
            'MY' => 'Malaysia',
            'MV' => 'Maldives',
            'ML' => 'Mali',
            'MT' => 'Malta',
            'MH' => 'Marshall Islands',
            'MQ' => 'Martinique',
            'MR' => 'Mauritania',
            'MU' => 'Mauritius',
            'YT' => 'Mayotte',
            'MX' => 'Mexico',
            'FM' => 'Micronesia, Federated States Of',
            'MD' => 'Moldova',
            'MC' => 'Monaco',
            'MN' => 'Mongolia',
            'ME' => 'Montenegro',
            'MS' => 'Montserrat',
            'MA' => 'Morocco',
            'MZ' => 'Mozambique',
            'MM' => 'Myanmar',
            'NA' => 'Namibia',
            'NR' => 'Nauru',
            'NP' => 'Nepal',
            'NL' => 'Netherlands',
            'AN' => 'Netherlands Antilles',
            'NC' => 'New Caledonia',
            'NZ' => 'New Zealand',
            'NI' => 'Nicaragua',
            'NE' => 'Niger',
            'NG' => 'Nigeria',
            'NU' => 'Niue',
            'NF' => 'Norfolk Island',
            'MP' => 'Northern Mariana Islands',
            'NO' => 'Norway',
            'OM' => 'Oman',
            'PK' => 'Pakistan',
            'PW' => 'Palau',
            'PS' => 'Palestinian Territory, Occupied',
            'PA' => 'Panama',
            'PG' => 'Papua New Guinea',
            'PY' => 'Paraguay',
            'PE' => 'Peru',
            'PH' => 'Philippines',
            'PN' => 'Pitcairn',
            'PL' => 'Poland',
            'PT' => 'Portugal',
            'PR' => 'Puerto Rico',
            'QA' => 'Qatar',
            'RE' => 'Reunion',
            'RO' => 'Romania',
            'RU' => 'Russian Federation',
            'RW' => 'Rwanda',
            'BL' => 'Saint Barthelemy',
            'SH' => 'Saint Helena',
            'KN' => 'Saint Kitts And Nevis',
            'LC' => 'Saint Lucia',
            'MF' => 'Saint Martin',
            'PM' => 'Saint Pierre And Miquelon',
            'VC' => 'Saint Vincent And Grenadines',
            'WS' => 'Samoa',
            'SM' => 'San Marino',
            'ST' => 'Sao Tome And Principe',
            'SA' => 'Saudi Arabia',
            'SN' => 'Senegal',
            'RS' => 'Serbia',
            'SC' => 'Seychelles',
            'SL' => 'Sierra Leone',
            'SG' => 'Singapore',
            'SK' => 'Slovakia',
            'SI' => 'Slovenia',
            'SB' => 'Solomon Islands',
            'SO' => 'Somalia',
            'ZA' => 'South Africa',
            'GS' => 'South Georgia And Sandwich Isl.',
            'ES' => 'Spain',
            'LK' => 'Sri Lanka',
            'SD' => 'Sudan',
            'SR' => 'Suriname',
            'SJ' => 'Svalbard And Jan Mayen',
            'SZ' => 'Swaziland',
            'SE' => 'Sweden',
            'CH' => 'Switzerland',
            'SY' => 'Syrian Arab Republic',
            'TW' => 'Taiwan',
            'TJ' => 'Tajikistan',
            'TZ' => 'Tanzania',
            'TH' => 'Thailand',
            'TL' => 'Timor-Leste',
            'TG' => 'Togo',
            'TK' => 'Tokelau',
            'TO' => 'Tonga',
            'TT' => 'Trinidad And Tobago',
            'TN' => 'Tunisia',
            'TR' => 'Turkey',
            'TM' => 'Turkmenistan',
            'TC' => 'Turks And Caicos Islands',
            'TV' => 'Tuvalu',
            'UG' => 'Uganda',
            'UA' => 'Ukraine',
            'AE' => 'United Arab Emirates',
            'GB' => 'United Kingdom',
            'US' => 'United States',
            'UM' => 'United States Outlying Islands',
            'UY' => 'Uruguay',
            'UZ' => 'Uzbekistan',
            'VU' => 'Vanuatu',
            'VE' => 'Venezuela',
            'VN' => 'Viet Nam',
            'VG' => 'Virgin Islands, British',
            'VI' => 'Virgin Islands, U.S.',
            'WF' => 'Wallis And Futuna',
            'EH' => 'Western Sahara',
            'YE' => 'Yemen',
            'ZM' => 'Zambia',
            'ZW' => 'Zimbabwe',
        );

        return $countries[$code];
    }


}
