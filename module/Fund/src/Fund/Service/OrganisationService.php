<?php

namespace Fund\Service;

/**
* OrganisationService
*/
class OrganisationService extends FundService
{
    protected $entityManager;

    public function getAllOrganisations()
    {
      $organisationRepository = $this->getEntityManager()->getRepository('Fund\Entity\Organisation');
      $organisations = $organisationRepository->findAll();
      return $organisations;
    }

    /**
     * Get organisation by url relative to /organisation
     *
     * @param  string $url
     * @return \Fund\Entity\Organisation
     */
    public function getOrganisationByUrl($url)
    {
        // get fund from repository
        $repository = $this->getEntityManager()
          ->getRepository('Fund\Entity\Organisation');
        $organisation = $repository->findOneBy(array('url' => $url));
        if (!$organisation) {
            throw new \Exception();
        }
        return $organisation;
    }


}
