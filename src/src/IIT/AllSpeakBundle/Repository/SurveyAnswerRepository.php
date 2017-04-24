<?php

namespace IIT\AllSpeakBundle\Repository;

use Doctrine\ORM\Query\ResultSetMapping;
use IIT\AllSpeakBundle\Entity\SurveySummary;
use IIT\AllSpeakBundle\Entity\SurveyAnswer;

/**
 * SurveyAnswerRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class SurveyAnswerRepository extends \Doctrine\ORM\EntityRepository
{
    public function count()
    {
        $qb = $this->createQueryBuilder('s');
        return $qb
            ->select('count(s.id)')
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getSurveySummary()
    {
        $em = $this->getEntityManager();

        if ($this->count() == 0)
            return null;

        $rsm = new ResultSetMapping();

        $rsm->addScalarResult('answersNum', 'answersNum');
        $rsm->addScalarResult('maleRatio', 'maleRatio');
        $rsm->addScalarResult('averageAge', 'averageAge');
        $rsm->addScalarResult('averageDiagnosisDate', 'averageDiagnosisDate');
        $rsm->addScalarResult('averageTimeSinceDiagnosis', 'averageTimeSinceDiagnosis');

        $query = $em->createNativeQuery('
          SELECT count(a.id) as answersNum,
          round(sum(a.gender = "M")/count(a.id)*100, 0) as maleRatio,
          round(avg(YEAR(CURDATE())  - a.birth_year), 0) as averageAge,
          from_unixtime(avg(UNIX_TIMESTAMP(a.diagnosis_date))) as averageDiagnosisDate
          FROM survey_answer a
          ', $rsm);

        $surveySummaryData = $query->getResult()[0];
        $averageDiagnosisDate = new \DateTime($surveySummaryData['averageDiagnosisDate']);
        $averageTimeSinceDiagnosis = (new \DateTime())->diff($averageDiagnosisDate);

        return new SurveySummary(
            $surveySummaryData['answersNum'],
            $surveySummaryData['maleRatio'],
            $surveySummaryData['averageAge'],
            $averageTimeSinceDiagnosis
        );
    }
}
