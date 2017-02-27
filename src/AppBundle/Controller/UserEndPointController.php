<?php

namespace AppBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;

use Doctrine\ORM\PersistentCollection;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/api")
 */
class UserEndPointController extends Controller
{

  /**
   * @Route("/user/list", name="admin_ajax_users_list")
   * @Template()
   */
  public function usersListAction(Request $request)
  {

    $get = $request->query->all();

    //var_dump($get);

    /* Array of database columns which should be read and sent back to DataTables. Use a space where
    * you want to insert a non-database field (for example a counter or static image)
    */
    $columns = array( 'id','username','name','email','city','addresse' );
    $get['fields'] = &$columns;

    $em = $this->getDoctrine()->getEntityManager();
    $rResult = $em->getRepository('AppBundle:User')->ajaxTable($get, true)->getArrayResult();



    /* Data set length after filtering */
    $iFilteredTotal = count($rResult);

    /*
     * Output
     */
    $output = array(
      "draw" => intval(array_key_exists ('draw',$get) ? $get['draw'] : 1 ),
      "totalRecords" => intval($em->getRepository('AppBundle:User')->getCount()),
      "totalDisplayRecords" => intval($iFilteredTotal),
      "data" => array()
    );

    foreach($rResult as $aRow)
    {
      $row = array();
      for ( $i=0 ; $i<count($columns) ; $i++ ){
        if ( $columns[$i] == "version" ){
          /* Special output formatting for 'version' column */
          $row[] = ($aRow[ $columns[$i] ]=="0") ? '-' : $aRow[ $columns[$i] ];
        }elseif ( $columns[$i] != ' ' ){
          /* General output */
          $row[] = $aRow[ $columns[$i] ];
        }
      }
      $output['data'][] = $row;
    }

    unset($rResult);

    $response = new Response(
      json_encode($output)
    );

    $response->headers->set('Content-Type','application/json');

    return $response;
  }


}
