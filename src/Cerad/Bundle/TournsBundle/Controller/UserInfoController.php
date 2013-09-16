<?php
namespace Cerad\Bundle\TournsBundle\Controller;

use Symfony\Component\HttpFoundation\Request;

use Cerad\Bundle\AccountBundle\Entity\User;

/* =============================================
 * Embedded controller for generating user information
 */
class UserInfoController extends BaseController
{
    public function showAction(Request $request)
    {
        // And the template (is this the view model?)
        $tplData = array();
        
        // The guest is eays
        if (!$this->hasRoleUser()) 
        {
            return $this->render('@CeradTourns/UserInfo/guest.html.twig',$tplData);
        }
        // Some standard stuff
        $user = $this->getUser();
        $tplData['user'] = $user;
        
        // Need this to support in memory users
        $personRepo = $this->get('cerad_person.repository');
        $person = null;
        
        // Need this to support in memory users
        if ($user instanceOf User)
        {
            $person = $personRepo->find($user->getPersonId());
        }
        else $person = null;
        
        if (!$person)
        {
            $person = $personRepo->newPerson();
            $person->setName('None');
        }
        $tplData['person'] = $person;
        
        return $this->render('@CeradTourns/UserInfo/user.html.twig',$tplData);        
    }
}
?>
