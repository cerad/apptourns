<?php
namespace Cerad\Bundle\TournsBundle\Officials;

use Cerad\Component\Excel\Excel;
use Cerad\Bundle\PersonBundle\DataTransformer\PhoneTransformer;

class ExportOfficialsXLS
{
    protected $excel;
    protected $phoneTransformer;
    
    protected $lodgingNightsHeaders;
    protected $availabilityDaysHeaders;
    
    public function __construct()
    {
        $this->excel = new Excel();
        $this->phoneTransformer = new PhoneTransformer();
    }
    protected function setColumnWidths($ws,$widths)
    {
        $col = 0;
        foreach($widths as $width)
        {
            $ws->getColumnDimensionByColumn($col++)->setWidth($width);
        }
    }
    protected function setRowValues($ws,$row,$values)
    {
        $col = 0;
        foreach($values as $value)
        {
            $ws->setCellValueByColumnAndRow($col++,$row,$value);
        }
    }
    protected function generateOfficialsSheet($ws,$project,$officials)
    {
        $ws->setTitle('Officials');
        
        $headers = array_merge(
            array(
                'ID','Status','Official','Email','Cell Phone','Age','Home City',
                'USSF ID','USSF State','Badge','Verified','Assess','Upgrading',
                'Team Aff','Team Desc',
            ),
            $this->lodgingNightsHeaders,
            array('LO With','TR From','TR With'),
            $this->availabilityDaysHeaders
        );
        $this->writeHeaders($ws,1,$headers);
        $row = 2;
        
        foreach($officials as $person)
        {
            $name        = $person->getName();
            $address     = $person->getAddress();
            $personFed   = $person->getFed($project->getFedRole());
            $cert        = $personFed->getCertReferee();
            $plan        = $person->getPlan($project->getId());
            $basic       = $plan->getBasic();
            
            $values = array();
            $values[] = $plan->getId();
            $values[] = $plan->getStatus();
          //$values[] = null; // $plans->getDateTimeCreated()->format('Y-m-d H:i');
            $values[] = $name->full;
            $values[] = $person->getEmail();
            $values[] = $this->phoneTransformer->transform($person->getPhone());
            
            $gender = $person->getGender();
            $age    = $person->getAge();
            $gage   = $gender . $age;
            $values[] = $gage;
            
            $city = $address->city . ', ' . $address->state;
            $values[] = $city;
            
            $values[] = 'R' . substr($personFed->getId(),5);
            $values[] = substr($personFed->getOrgKey(),5);
            $values[] = $cert->getBadge();
            $values[] = $cert->getBadgeVerified();
            
            $values[] = $basic['requestAssessment'];
            $values[] = $cert->getUpgrading();
            $values[] = $basic['teamClubAffilation'];
            $values[] = $basic['teamClubName'];
            
            foreach($basic['lodgingNights'] as $value)
            {
                $values[] = $value;
            }
            $values[] = $basic['lodgingWith'];
            $values[] = $basic['travelingFrom'];
            $values[] = $basic['travelingWith'];
            
            foreach($basic['availabilityDays'] as $value)
            {
                $values[] = $value;
            }
            $this->setRowValues($ws,$row++,$values);
        }
        // Done
        return;
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
    protected function generateLodgingSheet($ws,$project,$officials)
    {
        $ws->setTitle('Lodging');
        
        $headers = array_merge(
            array('Status','Official','Email','Cell Phone','Age','Home City'),
            $this->lodgingNightsHeaders,
            array('LO With','TR From','TR With'),
            $this->availabilityDaysHeaders
        );
        
        $this->writeHeaders($ws,1,$headers);
        $row = 2;
        
        foreach($officials as $person)
        {
            $address     = $person->getAddress();
            $plan        = $person->getPlan($project->getId());
            $basic       = $plan->getBasic();

            $needLodging = false;
            foreach($basic['lodgingNights'] as $value)
            {
                if ($value == 'Yes') $needLodging = true;
            }
            if (!$needLodging) continue;
            
            if (($plan->getStatus() == 'Rejected') || ($plan->getStatus() == 'Withdrew')) continue;
            
            $values = array();
            $values[] = $plan->getStatus();
            $values[] = $person->getName()->full;
            $values[] = $person->getEmail();
            $values[] = $this->phoneTransformer->transform($person->getPhone());
            
            $gender = $person->getGender();
            $age    = $person->getAge();
            $gage   = $gender . $age;
            $values[] = $gage;
            
            $city = $address->city . ', ' . $address->state;
            $values[] = $city;
            
            foreach($basic['lodgingNights'] as $value)
            {
                $values[] = $value;
            }
            $values[] = $basic['lodgingWith'];
            $values[] = $basic['travelingFrom'];
            $values[] = $basic['travelingWith'];
            
            foreach($basic['availabilityDays'] as $value)
            {
                $values[] = $value;
            }
            $this->setRowValues($ws,$row++,$values);
            
        }
        // Done
        return;
    }
    /* ==========================================================
     * Put the notes on their own sheer
     * Formatting tends to be ugly
     */
    protected function generateNotesSheet($ws,$project,$officials)
    {
        $ws->setTitle('Notes');

        $headers = array(
            'Status','Official','Email','Cell Phone','Badge','Verified','Notes');
        
        $this->writeHeaders($ws,1,$headers);
        $row = 2;
        
        foreach($officials as $person)
        {
            $personFed   = $person->getFed($project->getFedRole());
            $cert        = $personFed->getCertReferee();
            $plan        = $person->getPlan($project->getId());
            $basic       = $plan->getBasic();
            
            $values = array();
            $values[] = $plan->getStatus();
            $values[] = $person->getName()->full;
            $values[] = $person->getEmail();
            $values[] = $this->phoneTransformer->transform($person->getPhone());
            $values[] = $cert->getBadge();
            $values[] = $cert->getBadgeVerified();
            $values[] = $basic['notes'];
            
            $this->setRowValues($ws,$row++,$values);
        }
        // Done
        return;
    }
    /* ===================================================================
     * Deal with widths and such
     */
    protected $widths = array
    (
        'Status'       => 8,
        'Applied Date' => 16,
        'USSF ID'    => 18,
        'Official'   => 24,
        'Email'      => 24,
        'Cell Phone' => 14,
        'Age'        =>  4,
        'Badge'      =>  8,
        'Verified'   =>  4,
        'Notes'      => 72,
        'Home City'  => 16,
        'USSF State' =>  4,
      //'AV Fri'     =>  8,
      //'AV Sat'     =>  8,
      //'AV Sun'     =>  8,
      //'LO Fri'     =>  6,
      //'LO Sat'     =>  6,
        'LO With'    =>  8,
        'TR From'    =>  8,
        'TR With'    =>  8,
        'Assess'     =>  8,
        'Upgrading'  =>  8,
        'Team Aff'   => 10,
        'Team Desc'  => 10,
        'Level'      => 14,
        'LE CR'      =>  6,
        'LE AR'      =>  6,
    );
    protected function writeHeaders($ws,$row,$headers)
    {
        $col = 0;
        foreach($headers as $header)
        {
            if (isset($this->widths[$header])) $width = $this->widths[$header];
            else                               $width = 16;
            
            $ws->getColumnDimensionByColumn($col)->setWidth($width);
            $ws->setCellValueByColumnAndRow($col,$row,$header);
            $col++;
        }
    }
    /* ==========================================================
     * Main entry point
     */
    public function generate($project,$officials)
    {
        $basic = $project->getBasic();
        
        // Dynamic elements
        $this->lodgingNightsHeaders = array();
        foreach(array_keys($basic['lodgingNights']) as $key)
        {
            $name = 'LO ' . $key;
            $this->widths[$name] = 8;
            $this->lodgingNightsHeaders[] = $name;
        }
        $this->availabilityDaysHeaders = array();
        foreach(array_keys($basic['availabilityDays']) as $key)
        {
            $name = 'AV ' . $key;
            $this->widths[$name] = 6;
            $this->availabilityDaysHeaders[] = $name;
        }
        $this->ss = $ss = $this->excel->newSpreadSheet();
        
        $si = 0;
        
        $this->generateOfficialsSheet($ss->createSheet($si++),$project,$officials);
        $this->generateNotesSheet    ($ss->createSheet($si++),$project,$officials);
        $this->generateLodgingSheet  ($ss->createSheet($si++),$project,$officials);
        $this->generateAvailSheet    ($ss->createSheet($si++),$project,$officials);
        
        // Finish up
        $ss->setActiveSheetIndex(0);
        return $ss;
    }
    /* =======================================================
     * Older style but mught be still usefull
     * Called to get the content
     */
    protected $ss;
    
    public function getBuffer($ss = null)
    {
        if (!$ss) $ss = $this->ss;
        if (!$ss) return null;
        
        $objWriter = $this->excel->newWriter($ss); // \PHPExcel_IOFactory::createWriter($ss, 'Excel5');

        ob_start();
        $objWriter->save('php://output'); // Instead of file name
        
        return ob_get_clean();        
    }

}
?>
