<?php
/*
 * Timers Controller
 *---------------------------------
 * Timers Page Values
 * 1 => List
 * 2 => Add
 * 3 => View Certificate
 * 4 => Edit
 */
$COEPageURI['timers'][1] = "views/timers/list.php";
$COEPageURI['timers'][2] = "views/timers/new.php";
$COEPageURI['timers'][3] = "views/timers/certificate.php";
$COEPageURI['timers'][4] = "views/timers/edit.php";

$pageURL = get_site_url().'/timers/';

$COEPage = 1; 

if(!empty( $_REQUEST['calibration_calculation'] )) $COEPage = 2;
else if(!empty( $_REQUEST['show_calibration_certificate'] )) $COEPage = 3;
else if(!empty( $_REQUEST['edit_calibration_calculation'] )) $COEPage = 4;


if ($COEPage == 1) {
    $certicates = getCOETimerCertificatesList();
}else if($COEPage == 2){

    $validated = empty( $_REQUEST['form_ready_for_submit'] ) ? false : true;
    $updatedForm = empty( $_REQUEST['form_ready_for_update'] ) ? false : true;

    if ($validated || $updatedForm) {
        if($validated)$response = addTimerRecordings($_REQUEST);
        else if($updatedForm)$response = updateTimerRecordings($_REQUEST);

        if($response){
            $COEPage = 1;
            $certicates = getCOETimerCertificatesList();
        }
    }else{

        $newManufacturer = empty( $_REQUEST['manufacturer_name'] ) ? false : $_REQUEST['manufacturer_name'];
        addCOEManufacturer($newManufacturer);

        $newEquipment = empty( $_REQUEST['equipment_name'] ) ? false : $_REQUEST['equipment_name'];
        addCOEEquipment($newEquipment);

        $newSTEquipment = empty( $_REQUEST['s_t_equipment_name'] ) ? false : $_REQUEST['s_t_equipment_name'];
        addCOESTEquipment($newSTEquipment);

        $newClient = empty( $_REQUEST['client_name'] ) ? false : $_REQUEST['client_name'];
        addCOEClient($newClient);
        
        $clientID = empty( $_REQUEST['client_id'] ) ? false : $_REQUEST['client_id'];
        $newClientContactName = empty( $_REQUEST['contact_name'] ) ? false : $_REQUEST['contact_name'];
        $newClientContactEmail = empty( $_REQUEST['contact_email'] ) ? false : $_REQUEST['contact_email'];
        $newClientContactPhone = empty( $_REQUEST['contact_phone'] ) ? '' : $_REQUEST['contact_phone'];

        addCOEClientContact($clientID, $newClientContactName, $newClientContactEmail, $newClientContactPhone);

        $manufacturers = getCOEManufacturers();
        $equipments = getCOEEquipment();
        $STEquipments = getCOESTEquipment();
        $clients = getCOEClients();
        $clientContacts = getCOEClientContacts();

        // If this was an AJAX call, return json output then terminate execution
        $APICode = empty( $_REQUEST['api_code'] ) ? 0 : $_REQUEST['api_code'];

        switch ($APICode) {
            case '0':
                break;
            case '1':
                echo json_encode($manufacturers);
                exit();
                break;
            case '2':
                echo json_encode($equipments);
                exit();
                break;
            case '3':
                echo json_encode($STEquipments);
                exit();
                break;
            case '4':
                echo json_encode($clients);
                exit();
                break;
            case '5':
                echo json_encode($clientContacts);
                exit();
                break;
        }
    }

}else if($COEPage == 3){
    $requestedCertificate = $_REQUEST['ccc_id'];
    
    if(!empty( $_REQUEST['status'] )){  // Verify COE Timer Certificate
        verifyTimerCertificate($_REQUEST);
        exit();
    }else{                              //Show COE Timer Certificate
        $certification = getCOETimerCertificate($requestedCertificate);
    }
}else if($COEPage == 4){
    $requestedCertificate = $_REQUEST['ccc_id'];
    
    if(!empty( $_REQUEST['status'] )){  // Verify COE Timer Certificate
        verifyTimerCertificate($_REQUEST);
        exit();
    }else{                              //Show COE Timer Certificate
        $certification = getCOETimerCertificate($requestedCertificate);

        $manufacturers = getCOEManufacturers();
        $equipments = getCOEEquipment();
        $STEquipments = getCOESTEquipment();
        $clients = getCOEClients();
        $clientContacts = getCOEClientContacts();
    }
}

$currentUser = wp_get_current_user();

/*
 * Timer specific functions
 */

function addTimerRecordings($request){
    global $wpdb;

    $currentUser = wp_get_current_user();

    $testDetails = array(
        client_id => $request['client'],
        client_contact_id => $request['client_contact_id'],

        date_performed => $request['date_performed'],
        created_by => $currentUser->ID,
        manufacturer_id => $request['manufacturer'],
        equipment_id => $request['equipment'],
        equipment_model => $request['model'],
        equipment_serial_number => $request['serial_number'],
        submission_number => $request['submission_number'],

        certificate_number => 'NOT ISSUED',
        
        standard_test_equipment_id => $request['ste_equipment'],
        standard_test_equipment_manufacturer_id => $request['ste_manufacturer'],
        standard_test_equipment_model => $request['ste_model'],
        standard_test_equipment_serial_number => $request['ste_serial_number'],
        standard_test_equipment_certificate_number => $request['ste_certificate_number'],
        standard_test_equipment_sticker_number => $request['ste_sticker_number'],

        accuracy_of_standard => $request['accuracy_of_standard'],
        resolution_of_standard => $request['resolution_of_standard'],
        uncertainity_of_standard => $request['uncertainity_of_standard'],
        resolution_of_device_under_test => $request['resolution_of_device_under_test'],
        expected_set_point_a => $request['expected_set_point_a'],
        expected_set_point_b => $request['expected_set_point_b'],
        expected_set_point_c => $request['expected_set_point_c'],
        environmental_temperature => $request['environmental_temperature'],
        environmental_humidity => $request['environmental_humidity'],

        result => 'PENDING'

    );

    $wpdb->insert("wp_coe_timer_calculations", $testDetails);
    $calculationID = $wpdb->insert_id;

    $intervals = array(1, 2, 3, 4, 5);

    foreach ($intervals as $interval) {
        $intervalArray = array(
                'timer_calculation_id' => $calculationID,
                'reading_id' => $interval,
                'reading_a' => $request['reading_mm_1_'.$interval] * 60 + $request['reading_ss_1_'.$interval],
                'reading_b' => $request['reading_mm_2_'.$interval] * 60 + $request['reading_ss_2_'.$interval],
                'reading_c' => $request['reading_mm_3_'.$interval] * 60 + $request['reading_ss_3_'.$interval],
                'created_by' => $currentUser->ID
            );
        $wpdb->insert("wp_coe_timer_calculation_readings", $intervalArray);
    }

    return true;
}

function updateTimerRecordings($request){
    global $wpdb;

    $currentUser = wp_get_current_user();

    $calculationID = $request['calibration_calculation_id'];

    $testDetails = array(
        client_id => $request['client'],
        client_contact_id => $request['client_contact_id'],

        date_performed => $request['date_performed'],
        created_by => $currentUser->ID,
        manufacturer_id => $request['manufacturer'],
        equipment_id => $request['equipment'],
        equipment_model => $request['model'],
        equipment_serial_number => $request['serial_number'],
        submission_number => $request['submission_number'],

        certificate_number => 'NOT ISSUED',
        
        standard_test_equipment_id => $request['ste_equipment'],
        standard_test_equipment_manufacturer_id => $request['ste_manufacturer'],
        standard_test_equipment_model => $request['ste_model'],
        standard_test_equipment_serial_number => $request['ste_serial_number'],
        standard_test_equipment_certificate_number => $request['ste_certificate_number'],
        standard_test_equipment_sticker_number => $request['ste_sticker_number'],

        accuracy_of_standard => $request['accuracy_of_standard'],
        resolution_of_standard => $request['resolution_of_standard'],
        uncertainity_of_standard => $request['uncertainity_of_standard'],
        resolution_of_device_under_test => $request['resolution_of_device_under_test'],
        expected_set_point_a => $request['expected_set_point_a'],
        expected_set_point_b => $request['expected_set_point_b'],
        expected_set_point_c => $request['expected_set_point_c'],
        environmental_temperature => $request['environmental_temperature'],
        environmental_humidity => $request['environmental_humidity'],

        result => 'PENDING'

    );

    $wpdb->update("wp_coe_timer_calculations", $testDetails, ['id' => $calculationID]);

    $intervals = array(1, 2, 3, 4, 5);
    $now = date("Y-m-d H:i:s");

    foreach ($intervals as $interval) {
        $results = $wpdb->get_results("SELECT id FROM wp_coe_timer_calculation_readings WHERE timer_calculation_id = $calculationID AND reading_id = $interval", ARRAY_A);

        if (count($results > 0)) {
            $intervalArray = array(
                'reading_a' => $request['reading_mm_1_'.$interval] * 60 + $request['reading_ss_1_'.$interval],
                'reading_b' => $request['reading_mm_2_'.$interval] * 60 + $request['reading_ss_2_'.$interval],
                'reading_c' => $request['reading_mm_3_'.$interval] * 60 + $request['reading_ss_3_'.$interval],
                'updated_at' => $now
            );

            $wpdb->update("wp_coe_timer_calculation_readings", $intervalArray, ['id' => $results[0]['id']]);
        }else{
            $intervalArray = array(
                'reading_a' => $request['reading_mm_1_'.$interval] * 60 + $request['reading_ss_1_'.$interval],
                'reading_b' => $request['reading_mm_2_'.$interval] * 60 + $request['reading_ss_2_'.$interval],
                'reading_c' => $request['reading_mm_3_'.$interval] * 60 + $request['reading_ss_3_'.$interval],
                'timer_calculation_id' => $calculationID,
                'reading_id' => $interval
            );

            $wpdb->insert("wp_coe_timer_calculation_readings", $intervalArray);
        }
    }

    return true;
}

function getCOETimerCertificatesList(){
    global $wpdb;

    $query = "SELECT wp_coe_timer_calculations.id, 
                wp_coe_timer_calculations.date_performed, 
                wp_coe_clients.name AS client_name, 
                wp_coe_equipment.name AS equipment_name, 
                wp_coe_timer_calculations.equipment_serial_number, 
                wp_coe_timer_calculations.result 
            FROM wp_coe_timer_calculations 
            INNER JOIN wp_coe_equipment 
                ON wp_coe_timer_calculations.equipment_id = wp_coe_equipment.id
            INNER JOIN wp_coe_clients ON wp_coe_timer_calculations.client_id = wp_coe_clients.id;";

    return $wpdb->get_results($query);
}

function getCOETimerCertificate($certificateID){
    global $wpdb;

    $query = "SELECT wp_coe_timer_calculations.*,
                wp_coe_clients.name AS client_name,
                wp_coe_client_contacts.name AS client_contact_name,
                wp_coe_client_contacts.email AS client_contact_email,
                wp_coe_equipment.name AS equipment_name,
                wp_coe_manufacturers.name AS manufacturer_name,
                DATE_FORMAT(DATE_ADD(wp_coe_timer_calculations.date_performed, INTERVAL 1 YEAR),'%M %Y') AS certificate_validity
            FROM wp_coe_timer_calculations 
            LEFT JOIN wp_coe_clients 
                ON wp_coe_timer_calculations.client_id = wp_coe_clients.id
            LEFT JOIN wp_coe_client_contacts 
                ON wp_coe_timer_calculations.client_contact_id = wp_coe_client_contacts.id
            LEFT JOIN wp_coe_equipment 
                ON wp_coe_timer_calculations.equipment_id = wp_coe_equipment.id
            LEFT JOIN wp_coe_manufacturers
                ON wp_coe_timer_calculations.manufacturer_id = wp_coe_manufacturers.id
            WHERE wp_coe_timer_calculations.id = $certificateID;";

    $result = $wpdb->get_row($query);
    
    $subQuery = "SELECT * FROM wp_coe_timer_calculation_readings WHERE timer_calculation_id = ".$result->id;

    $result->readings = $wpdb->get_results($subQuery, ARRAY_A);

    // Creators, verifiers and approvers
    if ($result->created_by) {
        $subQuery = "SELECT display_name FROM wp_users WHERE ID = ".$result->created_by;

        $result->creator = $wpdb->get_row($subQuery, ARRAY_A);
    }else{
        $result->creator = "";
    }

    if ($result->verified_by) {
        $subQuery = "SELECT display_name FROM wp_users WHERE ID = ".$result->verified_by;

        $result->verifier = $wpdb->get_row($subQuery, ARRAY_A);
    }else{
        $result->verifier = "";
    }

    if ($result->approved_by) {
        $subQuery = "SELECT display_name FROM wp_users WHERE ID = ".$result->approved_by;

        $result->approver = $wpdb->get_row($subQuery, ARRAY_A);
    }else{
        $result->approver = "";
    }

    // Standard Test Equipment Info: Name and manufacturer
    $subQuery = "SELECT name FROM wp_coe_standard_test_equipment WHERE id = ".$result->standard_test_equipment_id;

    $result->ste_equipment = $wpdb->get_row($subQuery, ARRAY_A);

    $subQuery = "SELECT name FROM wp_coe_manufacturers WHERE id = ".$result->standard_test_equipment_manufacturer_id;

    $result->ste_manufacturer = $wpdb->get_row($subQuery, ARRAY_A);

    return $result;
}

function verifyTimerCertificate($data){
    global $wpdb;

    $currentUser = wp_get_current_user();
    $_APPROVER = $currentUser->ID;
    $_VERIFIER = $currentUser->ID;

    $datetime = date("Y-m-d H:i:s", time() + (3*60*60)); //UTC+3
    $year = substr($datetime, 0, 4);

    $subQuery = "SELECT COUNT(id) hits FROM wp_coe_timer_calculations WHERE verified_at LIKE '$year%'";

    $result = $wpdb->get_row($subQuery, ARRAY_A);
    $certificateNumber = str_pad((intval($result['hits'])+1), 4, "0", STR_PAD_LEFT);

    $verifierData = [
        'result' => $data['status'], 
        'certificate_number' => "COE/TIME/".$year."/$certificateNumber", 
        'verified_by' => $_VERIFIER, 
        'verified_at' => $datetime, 
        'approved_by' => $_APPROVER, 
        'approved_at' => $datetime
    ];

    $wpdb->update("wp_coe_timer_calculations", $verifierData, ['id' => $data['ccc_id']]);
}
?>