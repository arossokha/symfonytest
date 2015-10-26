<?php
namespace Art\JobtestBundle\DataFixtures\ORM;

use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Art\JobtestBundle\Entity\Affiliate;

class LoadAffiliateData extends AbstractFixture implements OrderedFixtureInterface
{
    public function load(ObjectManager $em)
    {
        $affiliate = new Affiliate();
        $affiliate->setUrl('http://sensio-labs.com/');
        $affiliate->setEmail('sension-labs@mailinator.com');
        $affiliate->setToken('sensio-labs');
        $affiliate->setIsActive(true);
        $affiliate->addCategory($em->merge($this->getReference('category-programming')));

        $em->persist($affiliate);

        $affiliate = new Affiliate();
        $affiliate->setUrl('/');
        $affiliate->setEmail('jobtest@mailinator.com');
        $affiliate->setToken('symfony');
        $affiliate->setIsActive(false);
        $affiliate->addCategory($em->merge($this->getReference('category-programming')), $em->merge($this->getReference('category-design')));

        $em->persist($affiliate);

        $em->flush();

        $this->addReference('affiliate',$affiliate);
    }

    public function getOrder()
    {
        return 3; // the order in which fixtures will be loaded
    }
}