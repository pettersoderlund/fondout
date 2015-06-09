<?php

namespace Fund\Service;

/**
* ConsoleService
*/
class ConsoleService extends FundService
{
    protected $entityManager;

    public function getEM()
    {
        return $this->getEntityManager();
    }

    public function findNavAverages($funds, $avgFund) {
      $year1   = 0;
      $year3   = 0;
      $year5   = 0;

      $count1  = 0;
      $count3  = 0;
      $count5  = 0;

      foreach ($funds as $fund) {
        if ($fund->nav1year) {
          $year1 += $fund->nav1year;
          $count1++;
        }

        if ($fund->nav3year) {
          $year3 += $fund->nav3year;
          $count3++;
        }

        if ($fund->nav5year) {
          $year5 += $fund->nav5year;
          $count5++;
        }
      }

      // Avoid zero division.
      if($count1 == 0) {
        $count1 = 1;
      }

      if($count3 == 0) {
        $count3 = 1;
      }

      if($count5 == 0) {
        $count5 = 1;
      }

      $precision = 2;

      $avgFund->setPercent1year(number_format($year1/$count1, $precision));
      $avgFund->setPercent3year(number_format($year3/$count3, $precision));
      $avgFund->setPercent5year(number_format($year5/$count5, $precision));

      return $avgFund;
    }

    /**
    * Get measure averages from given funds
    * Give funds
    * get averages for weapon, fossil and alcohol, tobacco, gambling
    */
    public function findMeasuredAverages($funds, $avgFund) {
      $weapon   = 0;
      $fossil   = 0;
      $alcohol  = 0;
      $tobacco  = 0;
      $gambling = 0;

      foreach ($funds as $fund) {
        $weapon   += $fund->getWeaponCompanies();
        $fossil   += $fund->getFossilCompanies();
        $alcohol  += $fund->getAlcoholCompanies();
        $tobacco  += $fund->getTobaccoCompanies();
        $gambling += $fund->getGamblingCompanies();
      }

      //To remove division by zero risk
      //set all avg to 0 if 0 funds given.
      if(sizeof($funds) == 0) {
        $fundCount = 1;
      } else {
        $fundCount = sizeof($funds);
      }

      $avgWeapon   = (int)($weapon/$fundCount);
      $avgFossil   = (int)($fossil/$fundCount);
      $avgAlcohol  = (int)($alcohol/$fundCount);
      $avgTobacco  = (int)($tobacco/$fundCount);
      $avgGambling = (int)($gambling/$fundCount);

      $avgFund->setWeaponCompanies($avgWeapon);
      $avgFund->setFossilCompanies($avgFossil);
      $avgFund->setAlcoholCompanies($avgAlcohol);
      $avgFund->setTobaccoCompanies($avgTobacco);
      $avgFund->setGamblingCompanies($avgGambling);

      return $avgFund;
    }
}
