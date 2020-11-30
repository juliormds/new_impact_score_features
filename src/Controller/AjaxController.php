<?php

namespace Drupal\new_impact_score_feature\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\AlertCommand;
use Drupal\Core\Database\Database;
use Drupal\user\Entity\User;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Session\AccountInterface;


class AjaxController extends ControllerBase{


	public function insert_data_to_db(){

    $db = Database::getConnection(); /** Get the database connection **/

    $uid = \Drupal::currentUser()->id(); /** Get current logged in user ID **/


    /** Query for grabbing Number of recent active days (180 days) and number of days since last login **/

    $query = $db->query('SELECT * FROM {users_field_data} WHERE uid = :uid', [':uid' => $uid]);

    $result = $query->fetchAll();

    /** Check if the user is in the database **/

    if(count($result) > 0){

      $current_time = time();

      $date1 = date('Y-m-d', $result[0]->created); /** Convert created timestamp to year, month, and day format **/

      $created_time = strtotime($date1);

      $datediff1 = $current_time - $created_time;

      $number_of_days_active = round($datediff1 / (60 * 60 * 24 * 180) );


      /** Number of days since last login **/

      $date2 = date('Y-m-d', $result[0]->login); /** Convert login timestamp to year, month, and day format **/

      $login_time = strtotime($date2);

      $datediff2 = $current_time - $login_time;

      $number_of_days_since_last_login = round($datediff2 / (60 * 60 * 24) );

      //echo "Number of active days " . $number_of_days_active . "<br />";
      //echo "Number of days since last login " . $number_of_days_since_last_login . "<br />";

    } else {
      $number_of_days_active = 0;
      $number_of_days_since_last_login = 0;
    }


      /** Query for grabbing Number of days since last upload **/

      $query2 = $db->query('SELECT * FROM {node_field_data} WHERE uid = :uid ORDER BY created DESC', [':uid' => $uid]);

      $result2 = $query2->fetchAll();

      /** Check if the user is in the database **/

      if(count($result2) > 0){

        $date3 = date('Y-m-d', $result2[0]->created);

        $start_time = strtotime($date3);

        $datediff3 = $current_time - $start_time;

        $num_days_last_upload = round($datediff3 / (60 * 60 * 24) );

        //echo "Number of days since last upload " . $num_days_last_upload. "<br />" ;

      } else {

        $num_days_last_upload = 0;

      }


    /** Query for grabbing Number of recent viewing others (180 days) **/

    $query3 = $db->query('SELECT * FROM {visitors} WHERE visitors_uid = :id', [':id' => $uid]);

    $result3 = $query3->fetchAll();

    /** Check if user is in the database **/

    if(count($result3) > 0){

      $date4 = date('Y-m-d', $result3[0]->visitors_date_time);

      $begin_time = strtotime($date4);

      $datediff4 = $current_time - $begin_time;

      $num_days_recent_viewing_others = round($datediff4 / (60 * 60 * 24 * 180) );

      //echo "Number of recent viewing others " . $num_days_recent_viewing_others ;

    } else {
      $num_days_recent_viewing_others = 0;
    }

    /** Update to the impact score details table **/

        $db = \Drupal::database()->update('impact_score_details')
          ->fields([
            'num_recent_active_days' => $number_of_days_active,
            'num_recent_viewing_others' => $num_days_recent_viewing_others,
            'num_days_last_login' => $number_of_days_since_last_login,
            'num_days_last_upload' => $num_days_last_upload,
          ])
          ->condition('uid', $uid)
          ->execute();


    return drupal_set_message("Data inserted");
	}
}
