<?php 
require 'vendor/autoload.php';
use Ramsey\Uuid\Uuid;


// get Available appointment slots using appointment data
function findAvailableTime($calendarIds, $duration, $periodToSearch, $timeSlotType = null)
{
    $searchDate = explode('/', $periodToSearch);

    $startDate = $searchDate[0];
    $endDate = $searchDate[1];
    $startDate1 = strtotime($startDate);
    $endDate1 = strtotime($endDate);
    $availabelSlotes = [];
    foreach ($calendarIds as $cardId) {
        if ($cardId == '48644c7a-975e-11e5-a090-c8e0eb18c1e9') {
            $appointmentsData = file_get_contents('calendar_data/joanna_hef.json');
            $appointmentsData = json_decode($appointmentsData, true);
        } else if ($cardId == '48cadf26-975e-11e5-b9c2-c8e0eb18c1e9') {
            $appointmentsData = file_get_contents('calendar_data/danny_boy.json');
            $appointmentsData = json_decode($appointmentsData, true);
           
        } else if ($cardId == '452dccfc-975e-11e5-bfa5-c8e0eb18c1e9') {
            $appointmentsData = file_get_contents('calendar_data/emma_win.json');
            $appointmentsData = json_decode($appointmentsData, true);
        } else {
            die('invalid calender Ids');
        }
        $appointments = $appointmentsData['appointments'];
        $availabelSlotes[$cardId] = [];
        // $timeslots = $appointmentsData['timeslots'];
        $bookedAppointmentForDate = [];
        foreach ($appointments as $appointment) {
            $appointmentStart = strtotime($appointment['start']);
            $appointmentEnd = strtotime($appointment['end']);
            if ($startDate1 <= $appointmentStart && $endDate1 >= $appointmentStart) {
                if($timeSlotType != null){
                    if($appointment['time_slot_type_id'] == $timeSlotType){
                        array_push($bookedAppointmentForDate, $appointment);
                    }
                }else{
                    array_push($bookedAppointmentForDate, $appointment);
                }
            }
        }
        
        $sDate = $startDate1;
        $eDate = $endDate1;
        usort($bookedAppointmentForDate, 'compareStart');
        
        if(count($bookedAppointmentForDate)>0){
            foreach($bookedAppointmentForDate as $bookedAppointment){
                $withDuration = $sDate + ($duration*60);
                $baStart = strtotime($bookedAppointment['start']);
                $baEnd = strtotime($bookedAppointment['end']);
                if (date('H:i', $withDuration) >= '20:30') {
                    $sDate = strtotime('tomorrow 08:00:00', $sDate);
                    $withDuration = $sDate + ($duration*60);
                }
                while($withDuration <= $baStart){
                    $uuid = Uuid::uuid4();
                    $s['id'] = $uuid->toString();
                    $s['calendar_id'] = $cardId;
                    $s['type_id'] = $timeSlotType;
                    $s['start'] = date('Y-m-d\TH:i:s', $sDate);
                    $s['end'] = date('Y-m-d\TH:i:s', $withDuration);
                    $s['public_bookable'] = true;
                    $s['out_of_office'] = false;
                    array_push($availabelSlotes[$cardId],$s);

                    $sDate = $withDuration;
                    $withDuration = $sDate + ($duration*60);
                    if (date('H:i', $withDuration) >= '20:30') {
                        $sDate = strtotime('tomorrow 08:00:00', $sDate);
                        $withDuration = $sDate + ($duration*60);
                        continue;
                    }
                }
                $sDate = $baEnd;
            }
        }
                    
            while($sDate <= $eDate){
                $withDuration = $sDate + ($duration*60);

                if (date('H:i', $withDuration) >= '20:30') {
                    $sDate = strtotime('tomorrow 08:00:00', $sDate);
                    continue;
                }
                $uuid = Uuid::uuid4();
                $s['id'] = $uuid->toString();
                $s['calendar_id'] = $cardId;
                $s['type_id'] = $timeSlotType;
                $s['start'] = date('Y-m-d\TH:i:s', $sDate);
                $s['end'] = date('Y-m-d\TH:i:s', $withDuration);
                $s['public_bookable'] = true;
                $s['out_of_office'] = false;
                array_push($availabelSlotes[$cardId],$s);
                $sDate = $withDuration;
            }
    } 
  
    return $availabelSlotes;
}

function compareStart($a, $b)
{
    return strtotime($a['start']) - strtotime($b['start']);
}

$appointmentSlots = findAvailableTime(['48644c7a-975e-11e5-a090-c8e0eb18c1e9','48cadf26-975e-11e5-b9c2-c8e0eb18c1e9'], 60, '2019-04-23T11:45:00Z/2019-04-23T20:15:00Z');

print_r(json_encode($appointmentSlots));

 


// get Available appointment slots using time slot data
function findAvailableTimeWithTimeSlot($calendarIds, $duration, $periodToSearch, $timeSlotType = null)
{
    $searchDate = explode('/', $periodToSearch);

    $startDate = $searchDate[0];
    $endDate = $searchDate[1];
    $startDate1 = strtotime($startDate);
    $endDate1 = strtotime($endDate);
    $availabelSlotes = [];
    foreach ($calendarIds as $cardId) {
        if ($cardId == '48644c7a-975e-11e5-a090-c8e0eb18c1e9') {
            $appointmentsData = file_get_contents('calendar_data/joanna_hef.json');
            $appointmentsData = json_decode($appointmentsData, true);
        } else if ($cardId == '48cadf26-975e-11e5-b9c2-c8e0eb18c1e9') {
            $appointmentsData = file_get_contents('calendar_data/danny_boy.json');
            $appointmentsData = json_decode($appointmentsData, true);
           
        } else if ($cardId == '452dccfc-975e-11e5-bfa5-c8e0eb18c1e9') {
            $appointmentsData = file_get_contents('calendar_data/emma_win.json');
            $appointmentsData = json_decode($appointmentsData, true);
        } else {
            die('invalid calender Ids');
        }
        $availabelSlotes[$cardId] = [];
        $timeslots = $appointmentsData['timeslots'];
        $timeSlotOld = [];
        $oldSdate = Null;
        $oldEdate = Null;
        foreach ($timeslots as $timeslot) {
            $appointmentStart = strtotime($timeslot['start']);
            $appointmentEnd = strtotime($timeslot['end']);
            $timeDifferenceInSeconds = $appointmentEnd - $appointmentStart;
            $timeDifferenceInMinutes = $timeDifferenceInSeconds / 60;
          
            if($appointmentStart >= $endDate1 || $appointmentEnd > $endDate1 || $appointmentStart <= $startDate1 || $appointmentEnd < $startDate1){
                continue;
            }
            if ($timeDifferenceInMinutes <= $duration) {
                
                if($timeSlotType != null){
                    if($timeslot['type_id'] == $timeSlotType){
                        array_push($availabelSlotes[$cardId], $timeslot);
                    }
                }else{
                    array_push($availabelSlotes[$cardId], $timeslot);
                }
                $oldSdate = Null;
                $oldEdate = Null;
                $timeSlotOld = [];

            }else{
                if($oldEdate != Null && $oldEdate == $appointmentStart){
                    $timeDifferenceInSeconds1 = $appointmentEnd - $oldSdate;
                    $timeDifferenceInMinutes1 = $timeDifferenceInSeconds1 / 60;
                    if($timeDifferenceInMinutes1 <= $duration){
                        $timeSlotOld[] = $timeslot;
                        if($timeSlotType != null){
                            if($timeslot['type_id'] == $timeSlotType){
                                array_merge($availabelSlotes[$cardId],$timeSlotOld);
                            }
                        }else{
                            array_merge($availabelSlotes[$cardId],$timeSlotOld);
                        }
                        $oldSdate = Null;
                        $oldEdate = Null;
                        $timeSlotOld = [];
                    }else{
                        $oldSdate = $oldSdate;
                        $oldEdate = $appointmentEnd;
                        $timeSlotOld[] = $timeslot;
                    }
                }else{
                    $timeSlotOld = [];
                    $oldSdate = $appointmentStart;
                    $oldEdate = $appointmentEnd;
                    $timeSlotOld[] = $timeslot;
                }
            }
            
        }
    }
    return $availabelSlotes;
}

$appointmentSlotsWithTimeSlot = findAvailableTimeWithTimeSlot(['48644c7a-975e-11e5-a090-c8e0eb18c1e9','48cadf26-975e-11e5-b9c2-c8e0eb18c1e9'], 60, '2019-04-23T11:45:00Z/2019-04-23T20:15:00Z');
print_r(json_encode($appointmentSlotsWithTimeSlot));