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
        return; if ($project);
    }
    /* ========================================================
     * Availability
     */
    protected function generateAvailSheet($ws,$project,$persons)
    {
        $ws->setTitle('Availability');
                
        $map1 = array(
            'Official'     => 'name',
        );
        $map2 = array(
            'Email'        => 'email',
            'Home City'    => 'city',
            'Badge'        => 'badge',
            
            'Level' => 'refereeLevel',
            'LE CR' => 'comfortLevelCenter',
            'LE AR' => 'comfortLevelAssist',
            
            'Assess'       => 'requestAssessment',
            'Upgrading'    => 'upgrading',
            'Team Aff'     => 'teamClubAffilation',
            'Team Desc'    => 'teamClubName',
        );
        $map = array_merge($map1,$this->availabilityMap,$map2);
      //$map = array_merge($map1);
        
        $row = 1;
        $this->setHeaders($ws,array_keys($map),$row);
        foreach($persons as $person)
        {
            $this->setRow($ws,$map,$person,$row);
        }
        return; if ($project);
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
            
            $lodging = isset($person['lodgingNights']) ? $person['lodgingNights'] : $person['lodging'];

            foreach($lodging as $value)
            {
                if ($value == 'Yes') $needLodging = true;
            }
            if (!$needLodging) continue;
            
            $this->setRow($ws,$map,$person,$row);
        }
        return; if ($project);
    }
    /* ==========================================================
     * Put the notes on their own sheer
     * Formatting tends to be ugly
     */
    protected function generateNotesSheet($ws,$project,$persons)
    {
        $ws->setTitle('Notes');

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
        return; if ($projects);
    }
    /* ==========================================================
     * Main entry point
     */
    public function generate($project,$officials)
    {
        $persons = $this->processOfficials($project,$officials);
        
        $this->ss = $ss = $this->createSpreadSheet();
        
        $si = 0;
        
        $this->generateOfficialsSheet($ss->createSheet($si++),$project,$persons);
        $this->generateNotesSheet    ($ss->createSheet($si++),$project,$persons);
        $this->generateLodgingSheet  ($ss->createSheet($si++),$project,$persons);
        $this->generateAvailSheet    ($ss->createSheet($si++),$project,$persons);
        
        // Finish up
        $ss->setActiveSheetIndex(0);
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
            
            $createdOn = $officialPlan->getCreatedOn();
            $person['appliedDate'] = $createdOn ? $createdOn->format('Y-m-d H:i') : null;
            
            $official = $officialPlan->getPerson();
            $person['name'] = $official->getName()->full;
            $person['email'] = $official->getEmail();
            $person['phone'] = $this->phoneTransformer->transform($official->getPhone());
            
            $person['gage'] = $official->getGender() . $official->getAge();
            
            $address = $official->getAddress();
            $person['city'] = sprintf('%s, %s', $address->city, $address->state);
            
            $fed  = $official->getFed($project->getFedRole());
            $cert = $fed->getCertReferee();
            
            $person['ussfId']        = 'R' . substr($fed->getFedKey(),5);
            $person['org']           = substr($fed->getOrgKey(),5);
            $person['badge']         = $cert->getBadge();
            $person['badgeVerified'] = $cert->getBadgeVerified();
            $person['upgrading']     = $cert->getUpgrading();
            
            $basic = $officialPlan->getBasic();
            
            $lodging = isset($basic['lodgingNights']) ? $basic['lodgingNights'] : $basic['lodging'];
            foreach($lodging as $key => $value)
            {
                $header = 'LO ' . $key;
                $index  = 'lo'  . $key;
                $this->columnWidths[$header] = 8;
                $this->lodgingMap[$header] = $index;
                $person[$index] = $value;
            }
          //print_r($basic); die();
            $avail = isset($basic['availabilityDays']) ? $basic['availabilityDays'] : $basic['availability'];
            foreach($avail as $key => $value)
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
