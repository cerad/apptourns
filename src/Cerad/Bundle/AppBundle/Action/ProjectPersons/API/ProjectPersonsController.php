<?php

namespace Cerad\Bundle\AppBundle\Action\ProjectPersons\API;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;

class ProjectPersonsController
{
    public function __construct($personRepo,$personConn)
    {
        $this->personRepo = $personRepo;
        $this->personConn = $personConn;
    }
    public function getAction(Request $request, $project)
    {
        $sql = <<<EOT
SELECT
    project_person.id          AS projectPersonId,
    project_person.project_id  AS projectId,
    project_person.person_name AS projectPersonName,
    project_person.created_on  AS createdOn,
    project_person.updated_on  AS updatedOn,
    person.id         AS personId,
    person.email      AS email,
    person.name_full  AS fullName,
    person.name_first AS firstName,
    person.name_last  AS lastName
FROM person_plans AS project_person
LEFT JOIN persons AS person ON person.id = project_person.person_id
WHERE project_person.project_id = :projectId
ORDER BY project_person.created_on
EOT;
        $stmt = $this->personConn->prepare($sql);
        $stmt->execute(['projectId' => $project->getKey()]);
        $rows = $stmt->fetchAll();
        
        $response = new JsonResponse($rows);
        $response->setCallback($request->query->get('callback'));
        return $response;
                
        print_r($rows[0]);
        die('Row count ' . count($rows));
    }
}