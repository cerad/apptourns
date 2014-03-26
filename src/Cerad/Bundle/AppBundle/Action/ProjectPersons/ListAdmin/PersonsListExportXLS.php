<?php
namespace Cerad\Bundle\AppBundle\Action\ProjectPersons\ListAdmin;

use Cerad\Bundle\CoreBundle\Excel\Export as ExcelExport;
use Cerad\Bundle\PersonBundle\DataTransformer\PhoneTransformer;

class PersonsListExportXLS extends ExcelExport
{
    protected $phoneTransformer;
    
    protected $lodgingMap;
    protected $availabilityMap;
    
    public function __construct()
    {
        $this->phoneTransformer = new PhoneTransformer();
    }
    protected $columnWidths = array
    (
        'ID'           => 4,
        'Status'       => 8,
        'Applied Date' => 16,
        'USSF ID'      => 18,
        'Official'     => 24,
        'Email'        => 24,
        'Cell Phone'   => 14,
        'Age'          =>  4,
        'Badge'        =>  8,
        'Verified'     =>  4,
        'Notes'        => 72,
        'Home City'    => 16,
        'USSF State'   =>  4,
        'LO With'  =>  8, 'TR From'   =>  8, 'TR With' =>  8,
        'Assess'   =>  8, 'Upgrading' =>  8,
        'Team Aff' => 10, 'Team Desc' => 10,
        'Level'    => 14, 'LE CR' =>  6, 'LE AR' =>  6,
    );
    protected function generateOfficialsSheet($ws,$project,$persons)
    {
        $ws->setTitle('Officials');
        
        $map1 = array(
            'ID'           => 'id',
            'Status'       => 'status',
            'Applied Date' => 'appliedDate',
            'Official'     => 'name',
            'Email'        => 'email',
            'Cell Phone'   => 'phone',
            'Age'          => 'gage',
            'Home City'    => 'city',
            'USSF ID'      => 'ussfId',
            'USSF State'   => 'org',
            'Badge'        => 'badge',
            'Verified'     => 'badgeVerified',
            'Assess'       => 'requestAssessment',
            'Upgrading'    => 'upgrading',
            'Team Aff'     => 'teamClubAffilation',
            'Team Desc'    => 'teamClubName',
        );
        $map2 = array(
            'LO With' => 'lodgingWith',
            'TR From' => 'travelingFrom',
            'TR With' => 'travelingWith',
        );
        $map = array_merge($map1,$this->lodgingMap,$map2,$this->availabilityMap);
        
        $row = 1;
        $this->setHeaders($ws,array_keys($map),$row);
        foreach($persons as $person)
        {
            $this->setRow($ws,$map,$person,$row);
        }
    }
    /* =============================================================
     * The availability
     */
    protected function generateAvailSheet($ws,$project,$officials)
    {
        $ws->setTitle('Availability');

        $headers = array_merge(
            array(
                'Official','Email','Cell Phone','Age',
                'Badge','Level','LE CR','LE AR','Assess','Upgrading',
                'Team Aff','Team Desc',
            ),
            $this->availabilityDaysHeaders
        );
        
        $this->writeHeaders($ws,1,$headers);
        $row = 2;
        
        foreach($officials as $person)
        {
            $personFed   = $person->getFed($project->getFedRoleId());
            $cert        = $personFed->getCertReferee();
            $plan        = $person->getPlan($project->getId());
            $basic       = $plan->getBasic();
            
            $values = array();
            $values[] = $person->getName()->full;
            $values[] = $person->getEmail();
            $values[] = $this->phoneTransformer->transform($person->getPhone());
            
            $gender = $person->getGender();
            $age    = $person->getAge();
            $gage   = $gender . $age;
            $values[] = $gage;
            
            $values[] = $basic['refereeLevel'];
            $values[] = $basic['comfortLevelCenter'];
            $values[] = $basic['comfortLevelAssist'];
            
            $values[] = $basic['requestAssessment'];
            $values[] = $cert->getUpgrading();
            $values[] = $basic['teamClubAffilation'];
            $values[] = $basic['teamClubName'];

            foreach($basic['availabilityDays'] as $value)
            {
                $values[] = $value;
            }
            $this->setRowValues($ws,$row++,$values);
        }
        // Done
        return;
    }
    /* ========================================================
     * Officials that have requested lodging
     */
    protected function generateLodgingSheet($ws,$project,$persons)
    {
        $ws->setTitle('Lodging');
        
        $map1 = array(
            'Applied Date' => 'appliedDate',
            'Official'     => 'name',
            'Email'        => 'email',
            'Cell Phone'   => 'phone',
            'Age'          => 'gage',
            'Home City'    => 'city',
        );
        $map2 = array(
            'LO With' => 'lodgingWith',
            'TR From' => 'travelingFrom',
            'TR With' => 'travelingWith',
        );
        $map = array_merge($map1,$this->lodgingMap,$map2,$this->availabilityMap);
        
        $row = 1;
        $this->setHeaders($ws,array_keys($map),$row);
        foreach($persons as $person)
        {
            $needLodging = false;
            foreach($person['lodgingNights'] as $value)
            {
                if ($value == 'Yes') $needLodging = true;
            }
            if (!$needLodging) continue;
            
            $this->setRow($ws,$map,$person,$row);
        }
    }
    /* ==========================================================
     * Put the notes on their own sheer
     * Formatting tends to be ugly
     */
    protected function generateNotesSheet($ws,$project,$persons)
    {
        $ws->setTitle('Notes');

        $headers = array(
            'Status','Official','Email','Cell Phone','Badge','Verified','Notes');
        
        $map = array(
            'ID'           => 'id',
            'Status'       => 'status',
            'Applied Date' => 'appliedDate',
            'Official'     => 'name',
            'Email'        => 'email',
            'Cell Phone'   => 'phone',
            'Badge'        => 'badge',
            'Verified'     => 'badgeVerified',
            'Notes'        => 'notes',
            'Upgrading'    => 'upgrading',
        );
        
        $row = 1;
        $this->setHeaders($ws,array_keys($map),$row);
        foreach($persons as $person)
        {
            if (!$person['notes']) continue;
            
            $this->setRow($ws,$map,$person,$row);
        }        
    }
    /* ==========================================================
     * Main entry point
     */
    public function generate($project,$officials)
    {
        $persons = $this->processOfficials($project,$officials);
        
        $plan = $project->getPlan();
        
        $this->ss = $ss = $this->createSpreadSheet();
        
        $si = 0;
        
        $this->generateOfficialsSheet($ss->createSheet($si++),$project,$persons);
        $this->generateNotesSheet    ($ss->createSheet($si++),$project,$persons);
        $this->generateLodgingSheet  ($ss->createSheet($si++),$project,$persons);
      //$this->generateAvailSheet    ($ss->createSheet($si++),$project,$officials);
        
        // Finish up
        $ss->setActiveSheetIndex(1);
        return $ss;
    }
    protected function processOfficials($project,$officialPlans)
    {
        $persons = array();
        
        foreach($officialPlans as $officialPlan)
        {
            $person = array();
            
            $person['id']     = $officialPlan->getId();
            $person['status'] = $officialPlan->getStatus();
            $person['appliedDate'] = $officialPlan->getCreatedOn()->format('Y-m-d H:i');
            
            $official = $officialPlan->getPerson();
            $person['name'] = $official->getName()->full;
            $person['email'] = $official->getEmail();
            $person['phone'] = $this->phoneTransformer->transform($official->getPhone());
            
            $person['gage'] = $official->getGender() . $official->getAge();
            
            $address = $official->getAddress();
            $person['city'] = sprintf('%s, %s', $address->city, $address->state);
            
            $fed  = $official->getFed($project->getFedRole());
            $cert = $fed->getCertReferee();
            
            $person['ussfId']        = 'R' . substr($fed->getId(),5);
            $person['org']           = substr($fed->getOrgKey(),5);
            $person['badge']         = $cert->getBadge();
            $person['badgeVerified'] = $cert->getBadgeVerified();
            $person['upgrading']     = $cert->getUpgrading();
            
            $basic = $officialPlan->getBasic();
            
            foreach($basic['lodgingNights'] as $key => $value)
            {
                $header = 'LO ' . $key;
                $index  = 'lo'  . $key;
                $this->columnWidths[$header] = 8;
                $this->lodgingMap[$header] = $index;
                $person[$index] = $value;
            }
            foreach($basic['availabilityDays'] as $key => $value)
            {
                $header = 'AV ' . $key;
                $index  = 'av'  . $key;
                $this->columnWidths[$header] = 6;
                $this->availabilityMap[$header] = $index;
                $person[$index] = $value;
            }
            $persons[] = array_merge($person,$basic);
        }
        return $persons;
    }
}
?>
