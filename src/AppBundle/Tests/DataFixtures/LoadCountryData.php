<?php
/**
 * Created by PhpStorm.
 * User: jean FRIRY
 * Date: 21/11/2015
 * Time: 23:35
 */

namespace AppBundle\Tests\DataFixtures;

use AppBundle\Entity\Country;
use Doctrine\Common\DataFixtures\AbstractFixture;
use Doctrine\Common\DataFixtures\OrderedFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;

class LoadCountryData extends AbstractFixture implements OrderedFixtureInterface
{
    /**
     * Load data fixtures with the passed EntityManager
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager)
    {
        $countryNames = array(
            'Afrique du Sud', 'Afghanistan', 'Albanie',
            'Algérie', 'Allemagne', 'Andorre',
            'Angola', 'Anguilla', 'Antarctique',
            'Antigua-et-Barbuda', 'Antilles Néerlandaises',
            'Arabie Saoudite', 'Argentine', 'Arménie',
            'Aruba', 'Australie', 'Autriche',
            'Azerbaïdjan', 'Bahamas', 'Bahreïn',
            'Bangladesh', 'Barbade', 'Bélarus',
            'Belgique', 'Belize', 'Bénin', 'Bermudes',
            'Bhoutan', 'Bolivie', 'Bosnie-Herzégovine',
            'Botswana', 'Brésil', 'Brunéi Darussalam',
            'Bulgarie', 'Burkina Faso', 'Burundi',
            'Cambodge', 'Cameroun', 'Canada',
            'Cap-vert', 'Chili', 'Chine', 'Chypre',
            'Colombie', 'Comores', 'Costa Rica',
            'Côte d\'Ivoire', 'Croatie', 'Cuba',
            'Danemark', 'Djibouti', 'Dominique',
            'Égypte', 'El Salvador', 'Émirats Arabes Unis',
            'Équateur', 'Érythrée', 'Espagne',
            'Estonie', 'États Fédérés de Micronésie',
            'États-Unis', 'Éthiopie', 'Fédération de Russie',
            'Fidji', 'Finlande', 'France', 'Gabon', 'Gambie',
            'Géorgie du Sud et les Îles Sandwich du Sud',
            'Géorgie', 'Ghana', 'Gibraltar', 'Grèce',
            'Grenade', 'Groenland', 'Guadeloupe',
            'Guam', 'Guatemala', 'Guinée Équatoriale',
            'Guinée-Bissau', 'Guinée', 'Guyana',
            'Guyane Française', 'Haïti', 'Honduras',
            'Hong-Kong', 'Hongrie', 'Île Bouvet',
            'Île Christmas', 'Île de Man', 'Île Norfolk',
            'Îles (malvinas) Falkland', 'Îles Åland',
            'Îles Caïmanes', 'Îles Cocos (Keeling)',
            'Îles Cook', 'Îles Féroé', 'Îles Heard et Mcdonald',
            'Îles Mariannes du Nord', 'Îles Marshall',
            'Îles Mineures Éloignées des États-Unis',
            'Îles Salomon', 'Îles Turks et Caïques',
            'Îles Vierges Britanniques',
            'Îles Vierges des États-Unis',
            'Inde', 'Indonésie', 'Iraq', 'Irlande',
            'Islande', 'Israël', 'Italie',
            'Jamahiriya Arabe Libyenne', 'Jamaïque',
            'Japon', 'Jordanie', 'Kazakhstan',
            'Kenya', 'Kirghizistan', 'Kiribati',
            'Koweït', 'L\'ex-République Yougoslave de Macédoine',
            'Lesotho', 'Lettonie', 'Liban', 'Libéria',
            'Liechtenstein', 'Lituanie', 'Luxembourg',
            'Macao', 'Madagascar', 'Malaisie',
            'Malawi', 'Maldives', 'Mali', 'Malte',
            'Maroc', 'Martinique', 'Maurice', 'Mauritanie',
            'Mayotte', 'Mexique', 'Monaco', 'Mongolie',
            'Montserrat', 'Mozambique', 'Myanmar',
            'Namibie', 'Nauru', 'Népal', 'Nicaragua',
            'Niger', 'Nigéria', 'Niué', 'Norvège',
            'Nouvelle-Calédonie', 'Nouvelle-Zélande',
            'Oman', 'Ouganda', 'Ouzbékistan',
            'Pakistan', 'Palaos', 'Panama',
            'Papouasie-Nouvelle-Guinée',
            'Paraguay', 'Pays-Bas', 'Pérou', 'Philippines',
            'Pitcairn', 'Pologne', 'Polynésie Française',
            'Porto Rico', 'Portugal', 'Qatar',
            'République Arabe Syrienne',
            'République Centrafricaine',
            'République de Corée', 'République de Moldova',
            'République Démocratique du Congo',
            'République Démocratique Populaire Lao',
            'République Dominicaine', 'République du Congo',
            'République Islamique d\'Iran',
            'République Populaire Démocratique de Corée',
            'République Tchèque', 'République-Unie de Tanzanie',
            'Réunion', 'Roumanie', 'Royaume-Uni', 'Rwanda',
            'Sahara Occidental', 'Saint-Kitts-et-Nevis',
            'Saint-Marin', 'Saint-Pierre-et-Miquelon',
            'Saint-Siège (état de la Cité du Vatican)',
            'Saint-Vincent-et-les Grenadines',
            'Sainte-Hélène', 'Sainte-Lucie',
            'Samoa Américaines', 'Samoa', 'Sao Tomé-et-Principe',
            'Sénégal', 'Serbie-et-Monténégro',
            'Seychelles', 'Sierra Leone', 'Singapour',
            'Slovaquie', 'Slovénie', 'Somalie', 'Soudan',
            'Sri Lanka', 'Suède', 'Suisse', 'Suriname',
            'Svalbard etÎle Jan Mayen', 'Swaziland',
            'Tadjikistan', 'Taïwan', 'Tchad',
            'Terres Australes Françaises',
            'Territoire Britannique de l\'Océan Indien',
            'Territoire Palestinien Occupé',
            'Thaïlande', 'Timor-Leste', 'Togo',
            'Tokelau', 'Tonga', 'Trinité-et-Tobago',
            'Tunisie', 'Turkménistan', 'Turquie',
            'Tuvalu', 'Ukraine', 'Uruguay', 'Vanuatu',
            'Venezuela', 'Viet Nam', 'Wallis et Futuna',
            'Yémen', 'Zambie', 'Zimbabwe'
        );

        foreach ($countryNames as $countryName) {
            $country = new Country();
            $country->setName($countryName);
            $manager->persist($country);
        }
        $manager->flush();
    }

    /**
     * Get the order of this fixture
     *
     * @return integer
     */
    public function getOrder()
    {
        return 1;
    }
}