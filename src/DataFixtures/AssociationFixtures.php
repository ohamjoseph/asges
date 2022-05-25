<?php

namespace App\DataFixtures;

require_once 'vendor/autoload.php';

use App\Entity\Association;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;

class AssociationFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $faker = Factory::create('fr_FR');
        for ($i = 0; $i < 100; $i++) {
            $association = new Association();
            $association->setNom($faker->text(20));
            $association->setNoRecipice($faker->randomNumber($nbDigits = NULL, $strict = false));
            $association->setObjectif($faker->text(40));
            $association->setDescription($faker->text(100));
            $association->setNbrAdherant($faker->randomDigit);
            $association->setAdresse($faker->streetAddress);
            $association->setTel($faker->countryCodePrefix = null | true | false, $formatting = null | true | false);
            $association->setMail($faker->email);

            $manager->persist($association);
        }
        $manager->flush();
    }
}
