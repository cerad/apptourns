<?php
namespace Cerad\Bundle\ArbiterBundle\Schedule\Tourn;

class SaveRefereeSchedule
{
    protected $refereeCurrent = null;
    
    protected function writePosition($file,$referee,$pos,$game)
    {
        if (!$referee) return;
        
        if ($this->refereeCurrent != $referee)
        {
            $this->refereeCurrent = $referee;
            fputcsv($file,array());
        }
      //switch($game->getDate())
      //{
      //    case '10/19/2012': $dow = 'FRI'; break;
      //    case '10/20/2012': $dow = 'SAT'; break;
      //    case '10/21/2012': $dow = 'SUN'; break;
      //    default:           $dow = 'DOW';
      //}
        
        $data = array
        (
            $referee,
            $pos,
            $game['num'],
            $game['date'],
            '',
            $game['dow'],
            $game['time'],
            $game['sport'],
            $game['level'],
            '','',
            $game['site'],
            $game['home'],
            $game['away'],
        );
        fputcsv($file,$data);
    }
    protected $referees = array();
    
    protected function addPosition($referee,$pos,$game)
    {
        if (!$referee) return;

        if ($referee == 'NA') return;
        
        $data = array('referee' => $referee, 'pos' => $pos,'game' => $game);
        
        if (!isset($this->referees[$referee])) $this->referees[$referee] = array();
        
        $this->referees[$referee][] = $data;
        
        return;
    }
    public function getRefereeForTeam($team)
    {
        switch($team)
        {
            case "HUNTSVILLE FUTBOL HUNTSVILLE FC BOYS '01 MAROON (A":   return 'Christopher Malone'; break;
            case 'FUSION FC FUSION 02 BOYS (AL)':                        return 'Eloy Corona'; break;
            case 'HUNTSVILLE FUTBOL 00 BOYS MAROON (AL)':                return 'Toby Linton'; break;
            
            case 'HUNTSVILLE FC GIRLS 01 RED (AL)':                      return 'John Sloan'; break;
            
            case 'HUNTSVILLE FUTBOL HFC 98 BOYS MAROON (AL)':            return 'Ralph Werling'; break;
            
            case 'HUNTSVILLE FC HFC 00 BLUE (AL)':        return 'Fred Thomas';   break;
            case 'HUNTSVILLE FUTBOL 00 BOYS MAROON (AL)': return 'Curtis Walker'; break;
            case 'HUNTSVILLE FC GIRLS 01 RED (AL))':      return 'Curtis Walker'; break;
            
            case 'CAMP FOREST FC CFFC 2K1 (TN)': return 'Adam Brooks'; break;
            
            case 'VESTAVIA ATTACK 98 BLACK (AL)': return 'John Mayer'; break;

            case '': return ''; break;
            case '': return ''; break;
        
        }
        return null;
    }
    public function save($fileName,$games)
    {
        $file = fopen($fileName,'wt');
        
        $headers = array('Referee','Pos',
            'Game','Date','DOW','Time', 'Sport','Level','Blah','Blah',
            'Site','Home-Team', 'Away-Team'
        );
        fputcsv($file,$headers);
        
        foreach($games as $game)
        {
            $this->addPosition($game['referee'],'CR',$game);
            $this->addPosition($game['ar1'],    'AR',$game);
            $this->addPosition($game['ar2'],    'AR',$game);
            

            //$referee = $this->getRefereeForTeam($game->getHomeTeam());
            //$this->addPosition($referee,'SPEC',$game);
            
            //$referee = $this->getRefereeForTeam($game->getAwayTeam());
            //$this->addPosition($referee,'SPEC',$game);

        }
        ksort($this->referees);
        
        foreach($this->referees as $referee)
        {
            foreach($referee as $position)
            {
                $this->writePosition($file,$position['referee'],$position['pos'],$position['game']);
            }
        }
        fclose($file);
    }
}

?>
